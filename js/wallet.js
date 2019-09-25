var cards = data.cards;
var account = data.account;
$( "#tx_dir" ).change(function() {
    var dir = $(this).val();
    $('#card').empty();
    if (dir == "CR") {
        $("#label").html("Source");
        
        for(var key in cards) {
            if (cards[key].id == 1) {
                var sel = ' selected';
                var def = ' (Default)';
                $("#type").val("CC");
            } else {
                var sel = '';
                var def = '';
            }
           var string = "<option"+sel+" value='"+cards[key].id+"' data-type='BA'>"+cards[key].name+def+"</option>";
           $('#card').append(string);
       }
       $("#amount_help").html("This will be the amount you are depositing to your wallet");
       $("#amount").attr("min", amt);
       $("#amount").attr("max", "");
    } else if (dir == "DR") {
        $("#label").html("Destination");
         
         for(var key in account) {
             if (account[key].id == 1) {
                 var sel = ' selected';
                 var def = ' (Default)';
                 $("#type").val("BA");
             } else {
                 var sel = '';
                 var def = '';
             }
            var string = "<option"+sel+" value='"+account[key].id+"' data-type='BA'>"+account[key].name+def+"</option>";
            $('#card').append(string);
        } 
         for(var key in cards) {
            var string = "<option value='"+cards[key].id+"' data-type='CC'>"+cards[key].name+"</option>";
            $('#card').append(string);
        } 
        
       $("#amount_help").html("This will be the amount you are removing from your wallet");
       $("#amount").attr("min", amt);
       $("#amount").attr("max", balance);
    }
});


$( "#card" ).change(function() {
    var type = $(this).find('option:selected').data("type");
    $("#type").val(type);
});