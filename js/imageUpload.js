$(".imgAdd").click(function(){
    var randomId = "uploadFile"+Math.floor((Math.random() * 100000000) + 1);
    $(this).closest(".row").find('.imgAdd').before('<div class="col-sm-2 imgUp"><div class="imagePreview"></div><label class="btn purple-bn1">Select File<input type="file" name="uploadFile[]" id="'+randomId+'" class="uploadFile img" value="Upload Photo" accept="image/*" style="width:0px;height:0px;overflow:hidden;" onchange="checkFileSize(event)"></label><i class="fa fa-times del"></i></div>');
  });
  $(document).on("click", "i.del" , function() {
      $(this).parent().remove();
  });
  $(function() {
      $(document).on("change",".uploadFile", function()
      {
              var uploadFile = $(this);
          var files = !!this.files ? this.files : [];
          if (!files.length || !window.FileReader) return; // no file selected, or no FileReader support
   
          if (/^image/.test( files[0].type)){ // only image file
              var reader = new FileReader(); // instance of the FileReader
              reader.readAsDataURL(files[0]); // read the local file
   
              reader.onloadend = function(){ // set image data as background of div
                  //alert(uploadFile.closest(".upimage").find('.imagePreview').length);
  uploadFile.closest(".imgUp").find('.imagePreview').css("background-image", "url("+this.result+")");
              }
          }
        
      });
  });


function setSelectedIndex(s, valsearch) {
    // Loop through all the items in drop down list
    for (i = 0; i< s.options.length; i++) { 
        if (s.options[i].value == valsearch) {
            // Item is found. Set its property and exit
            s.options[i].selected = true;
            break;
        }
    }
    return;
}

function setMultiSelectedIndex(s, data) {
    var main = data.split(",");
    // Loop through all the items in drop down list
    for (var j = 0; j < main.length; j++) {
        var opt = main[j];
        for (i = 0; i< s.options.length; i++) { 
            if (s.options[i].value == opt) {
                // Item is found. Set its property and exit
                s.options[i].selected = true;
                break;
            }
        }
    }
    return;
}