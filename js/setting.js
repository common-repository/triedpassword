
function deletes(id)
{
var answer = confirm('Are you sure you want to delete this?');
if (answer)
{
  var data = {
	action: 'test_response',
    did:id 	
		};
   	$.post(ipcall.ajaxurl, data, function(response) {
		//alert(response);
	});	
}
}
function test(id)
{  
   alert(id);
   var status=0;   
   if($("#ipc"+id).attr('checked'))
   if(document.getElementById("ipc"+id).checked)
   {	
    status=1;
   }else{
   status=0;
   }
    var data = {
	action: 'test_responses',
    ipstatus:status,
    rid:id 	
		};
   	$.post(ips.ajaxurl, data, function(response) {
		alert(response);
	});	
}
var count =1;
function updateip()
{
// $(function(){
    // $('.cbx').each(function(){
            // this.checked = !this.checked;
        // })
// jQuery.ajax({
        // type: 'POST',
        // url: ajaxloadpostajaxteam.ajaxurl,
        // data: {
            // action: 'ajaxloadpost_ajaxhandler',
            // ipstatus: val,
          
        // },
        // success: function(data, textStatus, XMLHttpRequest) {
      // alert(data);

		// },
         // error: function(MLHttpRequest, textStatus, errorThrown) {
             // alert('error ');
        // }
     // });
}
function showMessage()
{
  
  $("#wpdf >tr").remove();
  //$("#wpdf").remove();
  //$("#fmessage").append('<h1>Please Purchase License Key To Be Able To Avail All Feature</h1>');

$('#wpdf').replaceWith('<table><tr><td>Please Purchase License Key To Be Able To Avail All Feature</td></tr></table>');
 }