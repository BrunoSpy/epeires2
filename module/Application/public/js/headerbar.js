var headerbar = function(url){
	   $("select[name=zone]").on("change", function(event){
		   event.preventDefault();
		   $.post(url+'/savezone', $("#zoneform").serialize(), function(){
			  //refresh timeline instead of entire window
			  location.reload();
		   });
	   } );
	   
	   $("select[name=nameopsup]").on("change", function(event){
		   event.preventDefault();
		   $.post(url+'/saveopsup', $("#opsup").serialize(), function(data){
			   displayMessages(data);
		   }, 'json');
	   });
	   
	   $("select[name=nameipo]").on("change", function(event){
		   event.preventDefault();
		   $.post(url+'/saveipo', $("#ipo").serialize(), function(data){
			   displayMessages(data);
		   }, 'json');
	   });
};