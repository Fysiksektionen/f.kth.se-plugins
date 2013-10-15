jQuery(document).ready(function() {
	 var nextId = 1;
	 function addFormElement(desc="",tag="",type="",required=true,unique=false,elemId=-1) {
		 msg = '<li class="formElement widget">'+
				 '<input type="text" id="desc-'+nextId+'" name="descr[]" class="descInput" placeholder="Beskrivning" value="'+desc+'"><br>'+
				 '<label for="tag-'+nextId+'">Tagg</label>'+
				 '<select id="tag-'+nextId+'" name="tag[]">'+
					 '<option value=""'+(tag==""?" selected":"")+'><em>Ingen</em></option>'+
					 '<option value="namn"'+(tag=="namn"?" selected":"")+'>namn</option>'+
					 '<option value="dryck"'+(tag=="dryck"?" selected":"")+'>dryck</option>'+
					 '<option value="matpref"'+(tag=="matpref"?" selected":"")+'>matpref</option>'+
					 '<option value="arskurs"'+(tag=="arskurs"?" selected":"")+'>arskurs</option>'+
					 '<option value="telefon"'+(tag=="telefon"?" selected":"")+'>telefon</option>'+
				 '</select>'+
				 '<label for="type-'+nextId+'">Typ</label>'+
				 '<select id="type-'+nextId+'" name="type[]">'+
					 '<option value="text" '+(type=="text"?" selected":"")+'>Text</option>'+
					 '<option value="textifcheck" '+(type=="textifcheck"?" selected":"")+'>Check med extra textruta</option>'+
				 '</select>'+
				 '<input type="checkbox" id="required-'+nextId+'" name="required[]"'+(required?' checked':'')+' value="'+nextId+'">'+
				 '<label for="required-'+nextId+'">Obligatorisk</label>'+
				 '<input type="checkbox" id="unique-'+nextId+'" name="unique[]"'+(unique?' checked':'')+' value="'+nextId+'">'+
				 '<label for="unique-'+nextId+'">Kräv unik</label>'+
				 '<a href="#" class="deleteButton">Ta bort</a>'+
				 '<input type="hidden" name="localId[]" value="'+nextId+'">'+
				 '<input type="hidden" name="elemId[]" value="'+elemId+'">'+
				 '<input type="hidden" name="deleted[]" value="0" id="hiddenDelete-'+nextId+'">'+
			 '</li>';
			 jQuery(msg).hide().appendTo('#listOfFormElements').slideDown();
			 nextId++;
	 }
	 jQuery('#addButton').click(function(e) {
			 addFormElement();
			 return false;
	  });
	  jQuery('#listOfFormElements').on("click",'.deleteButton',function() {
		jQuery(this).parent().children("input[name='deleted[]']").attr("value","1");
		jQuery(this).parent().slideUp();
		return false;
	  });
	 //jQuery('#saveButton').button();
	 jQuery( "#listOfFormElements" ).sortable({ axis: "y" });
	 if(gasquereg.oldElements.length>0) {
		for(elem in gasquereg.oldElements) {
			addFormElement(
				gasquereg.oldElements[elem].description,
				gasquereg.oldElements[elem].tag,
				gasquereg.oldElements[elem].type,
				(gasquereg.oldElements[elem].is_required=="1"),
				(gasquereg.oldElements[elem].demand_unique=="1"),
				gasquereg.oldElements[elem].id
			);
		}
	 } else {
		addFormElement();//Add a default element
	}
	postboxes.add_postbox_toggles(pagenow);
 });