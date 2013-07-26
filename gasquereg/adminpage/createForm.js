jQuery(document).ready(function() {
	 var nextId = 1;
	 function addFormElement(desc="",tag="",type="",elemId=-1) {
		 msg = '<li class="formElement widget">'+
				 '<input type="text" id="desc-'+nextId+'" name="descr[]" class="descInput" placeholder="Beskrivning" value="'+desc+'"><br>'+
				 '<label for="tag-'+nextId+'">Tagg</label>'+
				 '<select id="tag-'+nextId+'" name="tag[]">'+
					 '<option value=""'+(tag==""?" selected":"")+'><em>Ingen</em></option>'+
					 '<option value="dryck"'+(tag=="dryck"?" selected":"")+'>dryck</option>'+
					 '<option value="matpref"'+(tag=="matpref"?" selected":"")+'>matpref</option>'+
					 '<option value="arskurs"'+(tag=="arskurs"?" selected":"")+'>arskurs</option>'+
				 '</select>'+
				 '<label for="type-'+nextId+'">Typ</label>'+
				 '<select id="type-'+nextId+'" name="type[]">'+
					 '<option value="text" '+(type=="text"?" selected":"")+'>Text</option>'+
				 '</select>'+
				 '<input type="hidden" name="elemId[]" value="'+elemId+'">'+
			 '</li>';
			 jQuery(msg).hide().appendTo('#listOfFormElements').fadeIn();
			 nextId++;
	 }
	 jQuery('#addButton').click(function(e) {
			 addFormElement();
			 return false;
	  });
	 //jQuery('#saveButton').button();
	 jQuery( "#listOfFormElements" ).sortable({ axis: "y" });
	 if(gasquereg.oldElements.length>0) {
		for(elem in gasquereg.oldElements) {
			addFormElement(gasquereg.oldElements[elem].description,gasquereg.oldElements[elem].tag,gasquereg.oldElements[elem].type,gasquereg.oldElements[elem].id);
		}
	 } else {
		addFormElement();//Add a default element
	}
 });