// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ButtonFunctions.js,v 1.25 2021/04/15 08:38:07 qvarin Exp $

define([
    'dojo/_base/declare',
    "dojo/ready", 
    "dojo/dom", 
    "dojo/dom-construct", 
    "dojo/dom-attr", 
    "dojo/number", 
    "dojo/query!css3", 
    "dojo/on", 
    "dijit/registry",
    "apps/pmb/contribution/datatypes/ResourceSelector",
    "apps/pmb/contribution/datatypes/MemorySelector",
    "dijit/form/TextBox",
    "dijit/form/Button",
    "dojo/_base/lang",
    'dojo/topic',
    'dojo/request',
], function(declare, ready, dom, domConstruct, domAttr, number, query, on, registry, ResourceSelector, MemorySelector, TextBox, Button, lang, topic, request){
	return declare(null, {
		formId : "",
		
		constructor : function(kwArgs) {
			
			
			lang.mixin(this, kwArgs);
			
			//add_resource_selector
			query("*[id $= \'_add_resource_selector\']", this.formId).forEach(lang.hitch(this,function(node) {
				var myWidget = node;				
				if (myWidget && !myWidget.alreadyParsed) {
					myWidget.alreadyParsed = 1;
					on(myWidget, "click", lang.hitch(this, this.onto_add_selector, myWidget));
				}			
			}));
			
			//add_responsability_selector
			query("*[id $= \'_add_responsability_selector\']", this.formId).forEach(lang.hitch(this,function(node) {
				var myWidget = node;
				if (myWidget && !myWidget.alreadyParsed) {
					myWidget.alreadyParsed = 1;
					on(myWidget, "click", lang.hitch(this, this.onto_add_selector, myWidget, true));
				}			
			}));
			
			//add_item_creator
			query("*[id $= \'_add_item_creator\']", this.formId).forEach(lang.hitch(this,function(node) {
				var myWidget = registry.byId(node.id);	
				if (myWidget && !myWidget.alreadyParsed) {
					myWidget.alreadyParsed = 1;
					on(myWidget, "click", lang.hitch(this, this.onto_add_item, myWidget));
				}			
			}));
			
			//add_linked_record
			query("*[id $= \'_add_linked_record\']", this.formId).forEach(lang.hitch(this,function(node) {
				var myWidget = registry.byId(node.id);	
				if (myWidget && !myWidget.alreadyParsed) {
					myWidget.alreadyParsed = 1;
					on(myWidget, "click", lang.hitch(this, this.onto_add_linked_record, myWidget));
				}			
			}));
			
			//add_multilingual_qualified
			query("*[id $= \'_add_multilingual_qualified\']", this.formId).forEach(lang.hitch(this,function(node) {
				var myWidget = node;	
				if (myWidget && !myWidget.alreadyParsed) {
					myWidget.alreadyParsed = 1;
					on(myWidget, "click", lang.hitch(this, this.onto_add_multilingual_qualified, myWidget));
				}			
			}));
			
			//add_card
			query("*[id $= \'_add_card\']", this.formId).forEach(lang.hitch(this,function(node) {
				var myWidget = registry.byId(node.id);
				if (myWidget && !myWidget.alreadyParsed) {
					myWidget.alreadyParsed = 1;
					on(myWidget, "click", lang.hitch(this, this.onto_add_card, myWidget))
				}
			}));
			
			//add
			query("*[id $= \'_add\']", this.formId).forEach(lang.hitch(this,function(node) {
				var myWidget = node;
				if (myWidget && !myWidget.alreadyParsed) {
					myWidget.alreadyParsed = 1;
					on(myWidget, "click", lang.hitch(this, this.onto_add_card, myWidget))
				}
			}));
		},
		
		onto_add_linked_record : function(myWidget) {
			var element_name = myWidget.elementName;
			var element_order = myWidget.elementOrder;
			
			var new_order_element = dom.byId(element_name+"_new_order");
			var old_order = number.parse(new_order_element.value);
			var lastElement = registry.byId(element_name+"_"+new_order_element.value+"_display_label");
			var new_order = number.parse(new_order_element.value)+1;
			new_order_element.value = new_order;
			
			var parent = dom.byId(element_name);
			var new_child="";
			
			const firstContainer = dom.byId(element_name+"_"+old_order);

			// Div container
			var new_container = domConstruct.create("div",{
			    id : element_name + "_" + new_order, 
			    "class" : "row"
			}, parent);

			// Div container flex
			if (firstContainer.hasChildNodes()) {
			    var children = firstContainer.children[0];
			    if (children && ( children.nodeName == "DIV" && children.classList.value.includes('contribution_area_flex'))) {
			        var flexContainer = lang.clone(children);
			        flexContainer.innerHTML = '';
			        new_container.appendChild(flexContainer);
			        
			        // On change de container
			        var old_container = new_container;
			        new_container = flexContainer;
			    }
			}
			
			// Input pour le sélecteur de ressource
			var input_value = domConstruct.create("input", {
				type : "text",
				list : element_name+"_"+new_order+"_display_label_list",
				autocomplete: "off"
			}, new_container)
			
			// Datalist
			domConstruct.create("datalist", {
				id : element_name+"_"+new_order+"_display_label_list"
			}, new_container);

			// Input hidden value
			domConstruct.create("input", {
				type : "hidden",
				id : element_name+"_"+new_order+"_value",
				name : element_name+"["+new_order+"][value]",
				value : "",
			}, new_container);
			
			// Selecteur relation type
			var old_relation_type = dom.byId(element_name+"["+old_order+"][relation_type]");
			if (old_relation_type) {
				var new_id = element_name+"["+new_order+"][relation_type]";
				var new_relation_type = lang.clone(old_relation_type);
				new_relation_type.id = new_id;
				new_relation_type.name = new_id;
				new_relation_type.select = "";
				new_relation_type.value = "";
				new_container.appendChild(new_relation_type);
			}
			
			// Input hidden type
			domConstruct.create("input", {
				type : "hidden",
				id : element_name+"_"+new_order+"_type",
				name : element_name+"["+new_order+"][type]",
				value : domAttr.get(dom.byId(element_name+"_0_type"), 'value')
			}, new_container);
			
			// Button delete
			var old_button = dom.byId(element_name+"_"+old_order+"_del");
			if (old_button) {
				var new_id = element_name+"_"+new_order+"_del";
				var delete_button = lang.clone(old_button);
				delete_button.type = "button";
				delete_button.id = new_id;
				delete_button.onclick = "onto_remove_selector_value('"+element_name+"','"+new_order+"')";
				new_container.appendChild(delete_button);
			}
			
			// Button search
			var old_button = dom.byId(element_name+"_"+old_order+"_search");
			if (old_button) {
			    var new_id = element_name+"_"+new_order+"_search";
			    var search_button = lang.clone(old_button);
			    search_button.id = new_id
			    new_container.appendChild(search_button);
			    topic.publish('ButtonFunction', 'addEventOnButton', {node: search_button});
			}
			
			// Button edit
			var new_id = element_name+"_"+new_order+"_edit";			
			var old_button = dom.byId(element_name+"_"+old_order+"_edit");			
			if (old_button){
								
				domConstruct.create("input", {
					type : "hidden",
					"class" : old_button.className,
					id: new_id,
					value : old_button.value,
					"data-edit_label": domAttr.get(old_button, "data-edit_label"),
					"data-form_url": domAttr.get(old_button, "data-form_url"),
					"data-linked_scenario": domAttr.get(old_button, "data-linked_scenario"),
					"data-form_property": domAttr.get(old_button, "data-form_property"),
				}, new_container);
				old_button.value += " / "+domAttr.get(old_button, "data-edit_label");
				topic.publish('ButtonFunction', 'addEventOnButton', {node: dom.byId(new_id)});
			}
			
			// Button add
			var old_button = dom.byId(element_name+"_"+old_order+"_sel");
			if (old_button) {
			    var new_id = element_name+"_"+new_order+"_sel";			
			    var add_button = lang.clone(old_button);
				add_button.type = "button";
			    add_button.id = new_id;
			    new_container.appendChild(add_button);
			    topic.publish('ButtonFunction', 'addEventOnButton', {node: add_button});
			}

			domConstruct.place(myWidget.domNode, new_container);
			
			// Resource Template
			var container = new_container;
			if (old_container) {
				var container = old_container;
			}
			domConstruct.create("div", {
				id : element_name + "_" + new_order + "_resource_template"
			}, container);

			// ResourceSelector
			new ResourceSelector({
				id : element_name+"_"+new_order+"_display_label",
				name : element_name+"["+new_order+"][display_label]",
				valueNodeId : element_name + "_" + new_order +"_value",
				templateNodeId : element_name + "_" + new_order +"_resource_template",
				completion : lastElement.completion,
				autexclude : lastElement.autexclude,
				param1 : lastElement.param1,
				param2 : lastElement.param2,
				handleAs : "json"
			}, input_value);
			
		},
		
		onto_add_selector : function(myWidget, responsability = false) {
			var element_name = domAttr.get(myWidget, "data-element-name");
			
			var new_order_element = dom.byId(element_name+"_new_order");
			var old_order = number.parse(new_order_element.value);
			var lastElement = registry.byId(element_name+"_"+new_order_element.value+"_display_label");
			var new_order = number.parse(new_order_element.value)+1;
			new_order_element.value = new_order;
			
			var parent = dom.byId(element_name);
			var new_child="";
			
			const firstContainer = dom.byId(element_name+"_"+old_order);

			// Div container
			var new_container = domConstruct.create("div",{
				id : element_name + "_" + new_order, 
				"class" : "row"
			}, parent);

			// Div container flex
			if (firstContainer.hasChildNodes()) {
				var children = firstContainer.children[0];
				if (children && ( children.nodeName == "DIV" && children.classList.value.includes('contribution_area_flex'))) {
					var flexContainer = lang.clone(children);
					flexContainer.innerHTML = '';
					new_container.appendChild(flexContainer);
					
					// On change de container
					var old_container = new_container;
					new_container = flexContainer;
				}
			}
			
			// Input pour le sélecteur de ressource
			var input_value = domConstruct.create("input", {
				type : "text",
				list : element_name+"_"+new_order+"_display_label_list",
				"class" : "form_row_content_resource_selector",
				autocomplete: "off"
			}, new_container)

			// Datalist
			domConstruct.create("datalist", {
				id : element_name+"_"+new_order+"_display_label_list"
			}, new_container);
			
			// Responsability selecteur de fonction 
			if (typeof responsability == "boolean" && responsability) {
				var function_selector = lang.clone(dom.byId(element_name+"_"+old_order+"_function_value"));
				function_selector.id = element_name+"_"+new_order+"_function_value";
				function_selector.name = element_name+"["+new_order+"][function_value]";
				function_selector.select = "";
				function_selector.value = "";
				new_container.appendChild(function_selector);
			}

			// Input hidden value
			domConstruct.create("input", {
				type : "hidden",
				id : element_name+"_"+new_order+"_value",
				name : element_name+"["+new_order+"][value]",
				value : "",
			}, new_container);
			
			// Input hidden type
			domConstruct.create("input", {
				type : "hidden",
				id : element_name+"_"+new_order+"_type",
				name : element_name+"["+new_order+"][type]",
				value : domAttr.get(dom.byId(element_name+"_"+old_order+"_type"), 'value')
			}, new_container);
			
			// Input hidden is_draft
			if (dom.byId(element_name+"_"+old_order+"_is_draft")){
				domConstruct.create("input", {
					type : "hidden",
					id : element_name+"_"+new_order+"_is_draft",
					name : element_name+"["+new_order+"][is_draft]",
					value :"0"
				}, new_container);
			}
			
			// Button delete
			var old_button = dom.byId(element_name+"_"+old_order+"_del");
			if (old_button) {
				var new_id = element_name+"_"+new_order+"_del";
				var delete_button = lang.clone(old_button);
				delete_button.type = "button";
				delete_button.id = new_id;
				delete_button.setAttribute('onclick', 'onto_remove_selector_value("' + element_name + '","' + new_order + '")');
				new_container.appendChild(delete_button);
			}
			
			// Button search
			var old_button = dom.byId(element_name+"_"+old_order+"_search");
			if (old_button) {
				var new_id = element_name+"_"+new_order+"_search";
				var search_button = lang.clone(old_button);
				search_button.id = new_id
				new_container.appendChild(search_button);
				topic.publish('ButtonFunction', 'addEventOnButton', {node: search_button});
			}
			
			// Button edit
			var new_id = element_name+"_"+new_order+"_edit";			
			var old_button = dom.byId(element_name+"_"+old_order+"_edit");			
			if (old_button){
				domConstruct.create("input", {
					type : "hidden",
					"class" : old_button.className,
					id: new_id,
					value : old_button.value,
					"data-edit_label": domAttr.get(old_button, "data-edit_label"),
					"data-form_url": domAttr.get(old_button, "data-form_url"),
					"data-linked_scenario": domAttr.get(old_button, "data-linked_scenario"),
					"data-form_property": domAttr.get(old_button, "data-form_property"),
				}, new_container);
				topic.publish('ButtonFunction', 'addEventOnButton', {node: dom.byId(new_id)});
			}
			
			// Button create
			var old_button = dom.byId(element_name+"_"+old_order+"_sel");
			if (old_button) {
				var new_id = element_name+"_"+new_order+"_sel";			
				var add_button = lang.clone(old_button);
				add_button.type = "button";
				add_button.id = new_id;
				new_container.appendChild(add_button);
				topic.publish('ButtonFunction', 'addEventOnButton', {node: add_button});
			}
			
			// Qualification de l'auteur
            if (dom.byId(element_name + "_composed_0_vedette_composee_apercu_autre")) {
                var pmb_name = '';
                switch (true) {
                	case element_name.includes('has_other_author'):
                		pmb_name = 'has_other_author';
                		break;
                	case element_name.includes('has_secondary_author'):
                		pmb_name = 'has_secondary_author';
                		break;
                	case element_name.includes('has_responsability_author'):
                		pmb_name = 'has_responsability_author';
                		break;
                	case element_name.includes('has_responsability_performer'):
                		pmb_name = 'has_responsability_performer';
                		break;
                	case element_name.includes('has_responsability_authperso'):
                		pmb_name = 'has_responsability_authperso';
                		break;
                	default:
                		pmb_name = 'has_main_author';
                		break;
                }
                var instance_name = element_name.split('_' + pmb_name)[0];
                
                var grammar = '';
                switch (element_name.split('_')[0]) {
	                case 'work':
	                	grammar = 'tu_authors';
	                	break;
	                case 'record':
	                	grammar = 'notice_authors';
                		break;
                	default:
                		grammar = 'rameau';
                		break;
                }
                
                var req = new http_request();
    			if (req.request('./ajax.php?module=ajax&categ=get_notice_form_vedette&grammar=' + grammar + '&pmb_name=' + pmb_name + '&instance_name=' + instance_name + '&index=' + new_order, 1)) {
    				// Il y a une erreur
    				alert(req.get_text());
    			} else {
    			 	var row_vedette = document.createElement('div');
    				row_vedette.className = 'row';
    				row_vedette.style.display = 'none';
    				row_vedette.setAttribute('id', element_name + '_' + new_order + '_vedette_selector');
    				row_vedette.innerHTML = req.get_text();
    				
    	        	var row = document.createElement('div');
    				row.className = 'row contribution_area_flex';
    				
    				var title = document.getElementById('contribution_vedette_title');
    				
    				var img_plus = document.createElement('img');
    				img_plus.name = 'img_plus' + new_order;
    				img_plus.className = 'img_plus';
    				img_plus.setAttribute('id', 'img_plus' + new_order + '_' + pmb_name);
    				img_plus.setAttribute('hspace', '3');
    				img_plus.setAttribute('border', '0');
    				img_plus.setAttribute('src', pmbDojo.images.getImage('plus.gif'));
    				img_plus.setAttribute('onclick', 'expand_vedette(this, \"' + element_name + '_' + new_order + '_vedette_selector\")');
    				
    				var apercu = document.createElement('input');
    				apercu.className = 'saisie-30emr';
    				apercu.setAttribute('name', element_name + '_composed_' + new_order + '_vedette_composee_apercu_autre');
    				apercu.setAttribute('id', element_name + '_composed_' + new_order + '_vedette_composee_apercu_autre');
    				apercu.setAttribute('type', 'text');
    				apercu.setAttribute('readonly', 'readonly');
    				
    				var old_button = dom.byId(element_name + "_" + old_order + "_del");
    				if (old_button) {
    					var del_vedette = lang.clone(old_button);
    					del_vedette.id = element_name + "_" + new_order + "_del_vedette";
						del_vedette.type = "button";
    					del_vedette.className = 'bouton';
    					del_vedette.setAttribute('type', 'button');
    					del_vedette.setAttribute('onclick', 'del_vedette(\"' + element_name + '\", ' + new_order + ')');
    				}
    				
    				row.appendChild(img_plus);
    				row.appendChild(document.createTextNode(' '));
    				row.appendChild(apercu);
    				row.appendChild(document.createTextNode(' '));
    				row.appendChild(del_vedette);
    				row.appendChild(document.createTextNode(' '));
    				
    				new_container.after(title, row, row_vedette);
    				eval(document.getElementById('vedette_script_' + pmb_name + '_composed_' + new_order).innerHTML);
    			}
            }
			
			domConstruct.place(myWidget, new_container);
			
			// Resource Template
			var container = new_container;
			if (old_container) {
				var container = old_container;
			}
			domConstruct.create("div", {
				id : element_name + "_" + new_order + "_resource_template"
			}, container);
			
			// ResourceSelector
			new ResourceSelector({
				id : element_name+"_"+new_order+"_display_label",
				name : element_name+"["+new_order+"][display_label]",
				valueNodeId : element_name + "_" + new_order +"_value",
				templateNodeId : element_name + "_" + new_order +"_resource_template",
				completion : lastElement.completion,
				autexclude : lastElement.autexclude,
				param1 : lastElement.param1,
				param2 : lastElement.param2,
				handleAs : "json"
			}, input_value);
		},
		
		onto_add_item : function(myWidget) {
			var element_name = myWidget.elementName;
			var element_order = myWidget.elementOrder;
			
			var new_order_element = dom.byId(element_name+"_new_order");
			var old_order = number.parse(new_order_element.value);
			var lastElement = registry.byId(element_name+"_"+new_order_element.value+"_display_label");
			var new_order = number.parse(new_order_element.value)+1;
			new_order_element.value = new_order;
			
			var parent = dom.byId(element_name);
			var new_child="";
			
			//div container
			var new_container = domConstruct.create("div",{
				id : element_name + "_" + new_order, 
				"class" : "row"
			}, parent);
			
			//input pour l'item creator
			var input_value = domConstruct.create("input", {
				type : "text",
				id : element_name+"_"+new_order+"_display_label",
				name : element_name+"["+new_order+"][display_label]",
				readonly: "readonly",
				autocomplete: "off"
			}, new_container);
			
			domConstruct.create("span", {
				innerHTML : '&nbsp;'
			}, new_container);
						
			//input value
			domConstruct.create("input", {
				type : "hidden",
				id : element_name+"_"+new_order+"_value",
				name : element_name+"["+new_order+"][value]",
				value : "",
			}, new_container);
			
			//input type
			domConstruct.create("input", {
				type : "hidden",
				id : element_name+"_"+new_order+"_type",
				name : element_name+"["+new_order+"][type]",
				value : domAttr.get(dom.byId(element_name+"_0_type"), 'value')
			}, new_container);
			
			//button delete
			var old_button = dom.byId(element_name+"_"+old_order+"_del");
			domConstruct.create("input", {
				type : "button",
				"class" : old_button.className,
				id: element_name+"_"+new_order+"_del",
				"class" : "bouton",
				value : "X",
				"onclick" : "onto_remove_selector_value('"+element_name+"','"+new_order+"')"
			}, new_container);
			
			//button search
			var new_id = element_name+"_"+new_order+"_search";
			var old_button_search = dom.byId(element_name+"_"+old_order+"_search");
			if (old_button_search){
				domConstruct.create("span", {
					innerHTML : '&nbsp;'
				}, new_container);
				
				domConstruct.create("input", {
					type : "button",
					"class" : old_button_search.className,
					id: new_id,
					value : old_button_search.value,
					"data-edit_label": domAttr.get(old_button_search, "data-edit_label"),
					"data-form_url": domAttr.get(old_button_search, "data-form_url"),
					"data-form_property": domAttr.get(old_button_search, "data-form_property"),
				}, new_container);
				topic.publish('ButtonFunction', 'addEventOnButton', {node: dom.byId(new_id)});
			}
			
			//button edit
			var new_id = element_name+"_"+new_order+"_edit";			
			var old_button = dom.byId(element_name+"_"+old_order+"_edit");			
			if (old_button){
				domConstruct.create("span", {
					innerHTML : '&nbsp;'
				}, new_container);
				
				domConstruct.create("input", {
					type : "hidden",
					"class" : old_button.className,
					id: new_id,
					value : old_button.value,
					"data-edit_label": domAttr.get(old_button, "data-edit_label"),
					"data-form_url": domAttr.get(old_button, "data-form_url"),
					"data-linked_scenario": domAttr.get(old_button, "data-linked_scenario"),
					"data-form_property": domAttr.get(old_button, "data-form_property"),
				}, new_container);
				old_button.value += " / "+domAttr.get(old_button, "data-edit_label");
				topic.publish('ButtonFunction', 'addEventOnButton', {node: dom.byId(new_id)});
			}

			//button créer
			var new_id = element_name+"_"+new_order+"_sel";
			var old_button = dom.byId(element_name+"_"+old_order+"_sel");
			if (old_button){
				domConstruct.create("span", {
					innerHTML : '&nbsp;'
				}, new_container);
				
				domConstruct.create("input", {
					type : "button",
					"class" : old_button.className,
					id: new_id,
					value : old_button.value,
					"data-edit_label": domAttr.get(old_button, "data-edit_label"),
					"data-form_url": domAttr.get(old_button, "data-form_url"),
					"data-form_property": domAttr.get(old_button, "data-form_property"),
				}, new_container);
				old_button.value += " / "+domAttr.get(old_button, "data-edit_label");
				topic.publish('ButtonFunction', 'addEventOnButton', {node: dom.byId(new_id)});
			}
			
			domConstruct.place(myWidget.domNode, new_container);
		},
		
		onto_add_card : function(myWidget) {
			var element_name = domAttr.get(myWidget, "data-element-name");
			var max_card = domAttr.get(myWidget, "data-element-order");
			
			var new_order_element = dom.byId(element_name+"_new_order");
			var old_order = number.parse(new_order_element.value);
			var new_order = number.parse(new_order_element.value)+1;
 			new_order_element.value = new_order;

			var parent = dom.byId(element_name);
 			
 			// Div container
			var new_container = domConstruct.create("div",{
				id : element_name + "_" + new_order, 
				"class" : "row contribution_area_flex"
			});	
			
			// Input value
			var old_input = dom.byId(element_name+"_"+old_order+"_value");
			if (old_input) {
				var new_input_value = lang.clone(old_input);
				new_input_value.id = element_name+"_"+new_order+"_value";
				new_input_value.name = element_name+"["+new_order+"][value]";
				new_input_value.value = "";
				new_container.appendChild(new_input_value);
			}

			// Button delete
			var old_button = dom.byId(element_name+"_"+old_order+"_del");
			if (old_button) {
				var new_id = element_name+"_"+new_order+"_del";
				var delete_button = lang.clone(old_button);
				delete_button.type = "button";
				delete_button.id = new_id;
				delete_button.onclick = "onto_remove_selector_value('"+element_name+"','"+new_order+"')";
				new_container.appendChild(delete_button);
			}
			
			// Input type 
			var old_input = dom.byId(element_name+"_"+old_order+"_type");
			if (old_input) {
				var new_input_value = lang.clone(old_input);
				new_input_value.id = element_name+"_"+new_order+"_type";
				new_input_value.name = element_name+"["+new_order+"][type]";
				new_container.appendChild(new_input_value);
			}
			
			parent.appendChild(new_container);
			
			// Button search
			var old_button = dom.byId(element_name+"_"+old_order+"_search");
			if (old_button) {
			    var new_id = element_name+"_"+new_order+"_search";
			    var search_button = lang.clone(old_button);
			    search_button.id = new_id
			    new_container.appendChild(search_button);
			    topic.publish('ButtonFunction', 'addEventOnButton', {node: search_button});
			}
			
			// Button edit
			var new_id = element_name+"_"+new_order+"_edit";			
			var old_button = dom.byId(element_name+"_"+old_order+"_edit");			
			if (old_button){
				domConstruct.create("input", {
					type : "hidden",
					"class" : old_button.className,
					id: new_id,
					value : old_button.value,
					"data-edit_label": domAttr.get(old_button, "data-edit_label"),
					"data-form_url": domAttr.get(old_button, "data-form_url"),
					"data-linked_scenario": domAttr.get(old_button, "data-linked_scenario"),
					"data-form_property": domAttr.get(old_button, "data-form_property"),
				}, new_container);
				old_button.value += " / "+domAttr.get(old_button, "data-edit_label");
				topic.publish('ButtonFunction', 'addEventOnButton', {node: dom.byId(new_id)});
			}

			// Button add
			var old_button = dom.byId(element_name+"_"+old_order+"_sel");
			if (old_button) {
			    var new_id = element_name+"_"+new_order+"_sel";			
			    var add_button = lang.clone(old_button);
				add_button.type = "button";
			    add_button.id = new_id;
			    new_container.appendChild(add_button);
			    topic.publish('ButtonFunction', 'addEventOnButton', {node: add_button});
			}
			
			domConstruct.place(myWidget, new_container);
		},
		onto_add_multilingual_qualified : function(myWidget) {
			var element_name = domAttr.get(myWidget, "data-element-name");
			var max_card = domAttr.get(myWidget, "data-element-order");
			
			var new_order_element = dom.byId(element_name+"_new_order");
			var new_order = number.parse(new_order_element.value)+1;
 			new_order_element.value = new_order;

			var parent = dom.byId(element_name);
 			//div container
			var new_container = domConstruct.create("div",{
				id : element_name + "_" + new_order, 
				"class" : "row contribution_area_flex"
			});	
			
			//input value
			var nodeValue = dom.byId(element_name+"_0_value");
			if (nodeValue) {
				var newNodeValue = lang.clone(nodeValue);
				newNodeValue.id = element_name+"_"+new_order+"_value";
				newNodeValue.name = element_name+"["+new_order+"][value]";
				newNodeValue.value = "";
				new_container.appendChild(newNodeValue);
			}
			
			//select qualification
			var nodeValue = dom.byId(element_name+"_0_qualification");
			if (nodeValue) {
				var newNodeValue = lang.clone(nodeValue);
				newNodeValue.id = element_name+"_"+new_order+"_qualification";
				newNodeValue.name = element_name+"["+new_order+"][qualification]";
				newNodeValue.value = "";
				new_container.appendChild(newNodeValue);
			}
			
			//select lang
			var nodeValue = dom.byId(element_name+"_0_lang");
			if (nodeValue) {
				var newNodeValue = lang.clone(nodeValue);
				newNodeValue.id = element_name+"_"+new_order+"_lang";
				newNodeValue.name = element_name+"["+new_order+"][lang]";
				newNodeValue.value = "";
				new_container.appendChild(newNodeValue);
			}
			
			//button delete
			var buttonDelete = new Button({
				id : element_name+"_"+new_order+"_del_card",
				type : "button",
				"class" : "bouton_small",
				label : "X",
				"onclick" : "onto_del_card('"+element_name+"','"+new_order+"')"
			});
			
			buttonDelete.placeAt(new_container);
			parent.appendChild(new_container);
		},
//		onto_add_merge_property : function(element_name,element_order) {
//			var newOrder = parseInt(domAttr.get(dom.byId(element_name+'_new_order'), 'value'))+1;
//			var mergePropertiesTemplate = window[element_name+'_template'];
//			//Tempo 
//			mergePropertiesTemplate = mergePropertiesTemplate.replace(/&amp;/g, '&');
//			mergePropertiesTemplate = mergePropertiesTemplate.replace(/&quot;/g, '\\"');
//			
//			mergePropertiesTemplate = mergePropertiesTemplate.replace(/&lt;/g, '<');
//			mergePropertiesTemplate = mergePropertiesTemplate.replace(/&gt;/g, '>');

//			mergePropertiesTemplate = mergePropertiesTemplate.replace(/\[0\]/g, '['+newOrder+']');
//			mergePropertiesTemplate = mergePropertiesTemplate.replace(/_0_/g, '_'+newOrder+'_');
//			mergePropertiesTemplate = mergePropertiesTemplate.replace(/_0/g, '_'+newOrder);

//			mergePropertiesTemplate = mergePropertiesTemplate.replace(/new_order/g, 'new_order_'+newOrder);
//			console.log('mergePropertiesTemplate', mergePropertiesTemplate);
			
//			var div = domConstruct.place(mergePropertiesTemplate, dom.byId(element_name));
//			parser.parse(div);
//			domAttr.set(dom.byId(element_name+'_new_order'), 'value', newOrder);
//		}
	})
});