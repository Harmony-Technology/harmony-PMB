<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RegistrationModel.php,v 1.54 2021/04/16 08:24:00 gneveu Exp $

namespace Pmb\Animations\Models;

use Pmb\Common\Models\Model;
use Pmb\Animations\Orm\RegistrationOrm;
use Pmb\Animations\Orm\RegistredPersonOrm;
use Pmb\Animations\Orm\PriceTypeCustomFieldValueOrm;
use Pmb\Common\Helper\Helper;
use Pmb\Common\Models\EmprModel;
use Pmb\Animations\Controller\MailingController;
use Pmb\Common\Helper\HashModel;
use Pmb\Animations\Orm\MailingTypeOrm;
use Pmb\Common\Models\MailtplModel;

class RegistrationModel extends Model
{
    public const QUOTA_GLOBAL = 0;
    public const QUOTA_INTERNET = 1;
    
    public const PENDING_VALIDATION = 1;
    public const VALIDATED = 2;
    public const WAITING_LIST = 3;
    
    protected $ormName = "\Pmb\Animations\Orm\RegistrationOrm";

    public static function getRegistrationsWaitingList(int $num_animation = 0)
    {
        $registrationsWaitingList = [];
        $registrations =  self::getRegistrations($num_animation);
        foreach ($registrations as $registration) {
            if ($registration->numRegistrationStatus == self::WAITING_LIST) {
                $registrationsWaitingList[] = $registration;
            }
        }
        return $registrationsWaitingList;        
    }
    
    public static function getOthersRegistrations(int $num_animation = 0)
    {
        $othersRegistrations = [];
        $registrations =  self::getRegistrations($num_animation);
        foreach ($registrations as $registration) {
            if ($registration->numRegistrationStatus != self::WAITING_LIST) {
                $othersRegistrations[] = $registration;
            }
        }
        return $othersRegistrations;        
    }
    
    public static function getRegistrations(int $num_animation = 0)
    {
        if ($num_animation){
            $registrationsList = RegistrationOrm::find('num_animation', $num_animation, 'num_registration_status, id_registration');
        } else {
            $registrationsList = RegistrationOrm::findAll();
        }
        foreach ($registrationsList as $key => $registration) {
            $registration = new RegistrationModel(intval($registration->id_registration));
            $registration->fetchRegistrationStatus();
            $registration->fetchAnimation();
            $registration->fetchValidated();
            $registration->getFormatDate();

            $registrationsList[$key] = $registration;
        }
        return self::toArray($registrationsList);
    }

    public static function getRegistration(int $id)
    {
        $registration = new RegistrationOrm($id);
        return $registration->toArray();
    }

    public static function deleteRegistration(int $id)
    {
        $maillingTypeOrm = MailingTypeOrm::find("periodicity", MailingTypeModel::MAILING_ANNULATION);
        
        $registration = new RegistrationOrm($id);
        $registrationModel = new RegistrationModel($id);
        $registration->delete();
        
        $registredPersonList = RegistredPersonOrm::find("num_registration", $id);
        foreach ($registredPersonList as $person) {
            PriceTypeCustomFieldValueOrm::deleteWhere("anim_price_type_custom_origine", $person->id_person);
            $person->delete();
        }
        
        // Generation du mail pour l'animation et la personne de contact
        if (!empty($maillingTypeOrm) && !empty($maillingTypeOrm[0])) {
            $template = MailtplModel::getMailtpl($maillingTypeOrm[0]->num_template);
            $temp = array();
            MailingAnimationModel::sendMail([$registrationModel], $registrationModel->numAnimation, $template, $temp, $maillingTypeOrm[0]->num_sender);
        }
    }

    public static function addRegistration(object $data)
    {
        if (empty($data->name) || empty($data->numAnimation) || empty($data->phoneNumber)) {
            return false;
        }
        
        if (empty($data->animationsSelected) && empty(AnimationModel::getDaughterList($data->numAnimation))) {
            $data->animationsSelected[] = $data->numAnimation;
        }
        
        // Dans le cas de base (simple) on dit que l'on doit passer par une reservation, puis une confirmation en Gestion ICI on g�re la reservation
        $maillingTypeOrm = MailingTypeOrm::find("periodicity", MailingTypeModel::MAILING_REGISTRATION);
        
        foreach ($data->animationsSelected as $idAnimation) {
            //a reprendre quand on gerera les statuts "en attente de validaton"
            $data->numRegistrationStatus = self::VALIDATED;
            
            $anim = new AnimationModel($idAnimation);
            if ($anim->allowWaitingList){
                $quotas = $anim->getAllQuotas($idAnimation);
                //Dans le cas d'une inscription local (et qu'il n'y � plus de place), on inscrit sur liste d'attente
                if ($quotas["animationQuotas"]["global"] != 0 && $quotas["availableQuotas"]["global"] < count($data->registrationListPerson)){
                    $data->numRegistrationStatus = self::WAITING_LIST;
                }
            }
            
            $registration = new RegistrationOrm();
            $registration->nb_registred_persons = count($data->registrationListPerson);
            $registration->name = $data->name;
            $registration->num_animation = $idAnimation;
            $registration->date = date('Y-m-d H:i:s');
            
            if (! empty($data->phoneNumber) && Helper::isValidPhone($data->phoneNumber)) {
                $registration->phone_number = $data->phoneNumber;
            }
            if (! empty($data->email) && Helper::isValidMail($data->email)) {
                $registration->email = $data->email;
            }
            if (! empty($data->numRegistrationStatus)) {
                $registration->num_registration_status = $data->numRegistrationStatus;
            }
            if (! empty($data->numEmpr)) {
                $registration->num_empr = $data->numEmpr;
            }
            if (! empty($data->numOrigin)) {
                $registration->num_origin = $data->numOrigin;
            }
            
            $registration->save();
            
            if (!empty($data->registrationListPerson)) {
                foreach ($data->registrationListPerson as $person){
                    $person->numRegistration = $registration->id_registration;
                    if (!empty($person->animations)) {
                        foreach ($person->animations as $numAnimation => $animations) {
                            if ($numAnimation == $idAnimation) {
                                $person->personCustomsFields = $animations->personCustomsFields;
                                $person->numAnimation = $animations->numAnimation;
                                $person->numPrice = $animations->numPrice;
                            }
                        }
                    }
                    RegistredPersonModel::addRegistredPerson($person);
                }
            }
            
            // Generation du mail pour l'animation et la personne de contact
            if (!empty($maillingTypeOrm) && !empty($maillingTypeOrm[0])) {
                $registrationModel = new RegistrationModel($registration->{RegistrationOrm::$idTableName});
                $template = MailtplModel::getMailtpl($maillingTypeOrm[0]->num_template);
                $temp = array();
                MailingAnimationModel::sendMail([$registrationModel], $idAnimation, $template, $temp, $maillingTypeOrm[0]->num_sender);
            }
        }
        
        return true;
    }

    public static function updateRegistration(int $id, object $data)
    {
        $registration = new RegistrationOrm($id);

        if (! empty($data->nbRegistredPersons)) {
            $registration->nb_registred_persons = $data->nbRegistredPersons;
        }
        if (! empty($data->name)) {
            $registration->name = $data->name;
        }
        if (! empty($data->email) && Helper::isValidMail($data->email)) {
            $registration->email = $data->email;
        } 
        if (! empty($data->phoneNumber) && Helper::isValidPhone($data->phoneNumber)) {
            $registration->phone_number = $data->phoneNumber;
        }
        if (! empty($data->numAnimation)) {
            $registration->num_animation = $data->numAnimation;
        }
        if (! empty($data->numRegistrationStatus)) {
            $registration->num_registration_status = $data->numRegistrationStatus;
        }
        if (! empty($data->numEmpr)) {
            $registration->num_empr = $data->numEmpr;
        }
        if (! empty($data->numOrigin)) {
            $registration->num_origin = $data->numOrigin;
        }
        
        $registration->save();
        
        $registrationModel = new RegistrationModel($registration->id_registration);
        $registrationModel->fetchRegistrationListPerson();
        
        foreach ($registrationModel->registrationListPerson as $registredPerson) {
            RegistredPersonModel::deleteRegistredPerson($registredPerson->idPerson);
        }
        
        if (!empty($data->registrationListPerson)) {
            foreach ($data->registrationListPerson as $person){
                $person->numRegistration = $registration->id_registration;
                RegistredPersonModel::addRegistredPerson($person);
            }
        }
        
        return $registration->id_registration;
    }

    public function fetchAnimation()
    {
        if (! empty($this->animation)) {
            return $this->animation;
        }
        $this->animation = null;
        if (! empty($this->numAnimation)) {
            $this->animation = new AnimationModel($this->numAnimation);
            $this->animation->fetchQuotas();
        }
        return $this->animation;
    }

    public function fetchRegistrationStatus()
    {
        if (! empty($this->registrationStatus)) {
            return $this->registrationStatus;
        }
        $this->registrationStatus = null;
        if (! empty($this->numRegistrationStatus)) {
            $this->registrationStatus = new RegistrationStatusModel($this->numRegistrationStatus);
        }
        return $this->registrationStatus;
    }

    public function fetchEmpr()
    {
        if (! empty($this->empr)) {
            return $this->empr;
        }
        $this->empr = null;
        if (! empty($this->numEmpr)) {
            $this->empr = new EmprModel($this->numEmpr);
        }
        return $this->empr;
    }

    public static function getFormData(int $numAnimation, string $numDaughtersAnimation = '')
    {
        $animationModel = new AnimationModel($numAnimation);
        $animationModel->fetchPrices();
        
        foreach ($animationModel->prices as $price) {
            $price->fetchPriceType();
        }
        $event = $animationModel->fetchEvent();
        $animationModel->event = $animationModel->getFormatDate($event);
        $animationModel->fetchLocation();
        $animationModel->fetchQuotas();
        $animationModel->checkChildrens();
        
        $formdata = [
            "animation" => $animationModel,
            "listDaughters" => AnimationModel::getDaughterList($numAnimation),
            'img' => [
                'plus' => get_url_icon('plus.gif'),
                'minus' => get_url_icon('minus.gif'),
                'expandAll' => get_url_icon('expand_all'),
                'collapseAll' => get_url_icon('collapse_all'),
                'tick' => get_url_icon('tick.gif'),
                'error' => get_url_icon('error.png'),
                'patience' => get_url_icon('patience.gif'),
                'sort' => get_url_icon('sort.png'),
                'iconeDragNotice' => get_url_icon('icone_drag_notice.png')
            ]
        ];
        
        $formdata["animationsSelected"] = [];
        if (! empty($numDaughtersAnimation)) {
            $formdata["animationsSelected"] = explode(',', $numDaughtersAnimation);
        }
        
        return $formdata;
    }

    public static function updateAnimationRegistration(int $id)
    {
        $registrationsList = RegistrationOrm::find("num_animation", $id);
        foreach ($registrationsList as $registrations){
            $registrations = new RegistrationOrm($registrations->id_registration);
            $registrations->num_animation = 0;
            $registrations->save();
        }
    }

    public static function deleteAnimationRegistration(int $id)
    {
        $registrationsList = RegistrationOrm::find("num_animation", $id);
        foreach ($registrationsList as $registration) {
            RegistredPersonModel::deleteRegistrationRegistredPerson($registration->id_registration);
            $registration->delete();
        }
    }

    public function fetchRegistrationListPerson()
    {
        $this->registrationListPerson = RegistredPersonModel::getListPersonFromRegistration($this->idRegistration);
        return $this->registrationListPerson;
    }

    public static function getRegistrationPlaceForAnimation($idAnimation)
    {
        $registrationOrm = new RegistrationOrm();
        $registrations = [];
        //A reprende lors de la prise en compte de la mod�ration des inscriptions
        $registrations['global'] = $registrationOrm->find("num_animation",$idAnimation . "' AND num_origin = '" . self::QUOTA_GLOBAL . "' AND num_registration_status != '" . self::WAITING_LIST);
        $registrations['internet'] = $registrationOrm->find("num_animation",$idAnimation . "' AND num_origin = '". self::QUOTA_INTERNET . "' AND num_registration_status != '" . self::WAITING_LIST);
        
        return $registrations;
    }

    public static function getRegistrationWaitingList($idAnimation)
    {
        $registrationOrm = new RegistrationOrm();
        $registrations = [];
        
        $registrations['global'] = $registrationOrm->find("num_animation",$idAnimation . "' AND num_origin = '" . self::QUOTA_GLOBAL . "' AND num_registration_status = '" . self::WAITING_LIST);
        $registrations['internet'] = $registrationOrm->find("num_animation",$idAnimation . "' AND num_origin = '". self::QUOTA_INTERNET . "' AND num_registration_status = '" . self::WAITING_LIST);
        
        return $registrations;
    }
    
    public static function getRegistrationList($idRegistration = 0, $numAnimation = 0)
    {
        $registration = new RegistrationModel($idRegistration);
        
        if (empty($idRegistration)) {
            $registration->numAnimation = intval($numAnimation);
            $registration->barcode = '';
            $registration->registrationListPerson = array();
        } else {
            $registration->fetchRegistrationListPerson();
            $empr = new EmprModel($registration->numEmpr);
            $registration->barcode = $empr->emprCb;
        }
        
        return $registration;
    }
    
    public static function validateRegistration(int $id)
    {
        $registrationOrm = new RegistrationOrm($id);
        if ($registrationOrm->num_registration_status === self::VALIDATED) {
            return ;
        }
        $registrationOrm->num_registration_status = self::VALIDATED;
        $registrationOrm->save();
        
        $registration = new RegistrationModel($id);
        
        // Dans le cas de base (simple) on dit que l'on doit passer par une reservation, puis une confirmation en Gestion, ICI on g�re la confirmation
        $maillingTypeOrm = MailingTypeOrm::find("periodicity", MailingTypeModel::MAILING_CONFIRMATION);
        
        // Generation du mail pour l'animation et la personne de contact
        if (!empty($maillingTypeOrm) && !empty($maillingTypeOrm[0])) {
            $template = MailtplModel::getMailtpl($maillingTypeOrm[0]->num_template);
            $temp = array();
            MailingAnimationModel::sendMail([$registration], $registration->numAnimation, $template, $temp, $maillingTypeOrm[0]->num_sender);
        }
        
        MailingController::proceedSms('registration', 0, $registration);
    }
    
    public function fetchValidated()
    {
        $this->validated = boolval($this->numRegistrationStatus == self::VALIDATED) ;
        return $this->validated;
    }
    
    public function getFormatDate() 
    {
        $this->rawDate = $this->date;
        $date = new \DateTime($this->date);
        $this->date = $date->format("d/m/Y")." ".$date->format("H:i");
        return $this->date;
    }
    
    public static function getEmprRegistrationsList($emprId)
    {
        $registredPersonsList = RegistredPersonModel::getRegistredPersonsByEmpr($emprId);
        
        $registrations = [];
        foreach ($registredPersonsList as $registredPerson) {
            
            $registration = new RegistrationModel($registredPerson['num_registration']);
            $registration->fetchAnimation();
            $registration->animation->getViewData();
            
            if (empty($registration->animation->event->dateExpired)) {
                $registration->animation->event->dateExpired = false;
            }
            
            if (!empty($registration->animation->event) && !$registration->animation->event->dateExpired) {
                $registrations[] = $registration;
            }
        }
        
        return $registrations;
    }
    
    public static function getIdRegistrationFromEmprAndAnimation($idEmpr, $idAnimation) {
        if (! empty($idEmpr)) {
            $registrationOrm = new RegistrationOrm();
            $instances = $registrationOrm->find("num_animation", $idAnimation . "' AND num_empr = '$idEmpr");
            
            if (!empty($instances)) {
                $registration = $instances[0];
            } else {
                $registredPersonOrm = new RegistredPersonOrm();
                $instances = $registredPersonOrm->find("num_empr", intval($idEmpr));
                foreach ($instances as $instance) {
                    $registrationOrm = new RegistrationOrm($instance->num_registration);
                    if ($idAnimation == $registrationOrm->num_animation) {
                        $registration = $registrationOrm;
                        break;
                    }
                }
            }
            
            if (!empty($registration)) {
                return (int) $registration->id_registration ?? 0;
            }
        }
        
        return 0;
    }
    
    public function getViewData(int $emprId = 0)
    {
        $this->registredPersons = RegistredPersonModel::getListPersonFromRegistration($this->id);
        
        // Lien de d�sinscription pour la personne pr�sente
        $this->unsubscribeLink = "";
        
        if (!empty($this->idRegistration) && !empty($emprId)) {
            
            $this->is_contact = false;
            if ($this->numEmpr == $emprId) {
                $this->is_contact = true;
            }
            
            foreach ($this->registredPersons as $registredPerson) {
                if ($registredPerson->numEmpr == $emprId) {
                    $this->unsubscribeLink = $registredPerson->getUnsubscribeLink();
                    break;
                }
            }
            
            if (empty($this->unsubscribeLink)) {
                $this->unsubscribeLink = $this->getContactUnsubscribeLink();
            }
        }
        
        return $this;
    }
    
    public function getContactUnsubscribeLink()
    {
        global $opac_url_base;
        
        if (!empty($this->unsubscribeLink)) {
            return $this->unsubscribeLink;
        }
        
        $this->unsubscribeLink = $opac_url_base."index.php?lvl=registration&action=delete&id_registration=".intval($this->idRegistration);
        if (empty($this->hash)) {
            $this->generateHash();
        }
        $this->unsubscribeLink .= "&hash=".$this->hash;
        
        return $this->unsubscribeLink;
    }
    
    public function generateHash() 
    {
        $param = $this->idRegistration.$this->date.$this->numAnimation;
        $hashModel = new HashModel();
        $this->hash = $hashModel->generateHash($param);
        
        $registrationOrm = new RegistrationOrm($this->idRegistration);
        $registrationOrm->hash = $this->hash;
        $registrationOrm->save();
        
        return $this->hash;
    }
    
    public static function deleteFromCirculation($idEmpr) {
        $registrationOrm = new RegistrationOrm();
        $instances = $registrationOrm->find("num_empr", $idEmpr);
        foreach ($instances as $registration){
            $registration->num_empr = 0;
            $registration->save();
        }
        
        $RegistredPersonOrm = new RegistredPersonOrm();
        $instances = $RegistredPersonOrm->find("num_empr", $idEmpr);
        foreach ($instances as $registration){
            $registration->num_empr = 0;
            $registration->save();
        }
    }
    
}