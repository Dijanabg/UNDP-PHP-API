function prikazi() {
  var x = document.getElementById("pregled");
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}

$("#btn-izbrisi").click(function (e) {
  e.preventDefault()
  console.log("Izbrisi pokrenuto")
    const checked = $("input[type=radio]:checked");
    request = $.ajax({
      url: "handler/delete.php",
      type: "delete",
      data:{"timID":checked.val()}
    });
    request.done(function (response, textStatus, jqXHR) {
      
        checked.closest("tr").remove();
        console.log("Tim je obrisan ");
        alert("Tim je obrisan");
        $('#izmeniForm').reset();
    });
    request.fail(function (jqXHR, textStatus, errorThrown) {
      console.error("Sledeca greska se desila: " + textStatus, errorThrown);
      console.log(jqXHR);
    })
  });
/////////////////////
  $("#dodajForm").submit(function (e) { 
    e.preventDefault();
    console.log("Dodaj novi tim zapoceto...")

    const $form=$(this)
    // console.log($form);

    // let obj = $form.serializeArray();
    // console.log(obj)

    const serializeData =$form.serialize()

    

    request=$.ajax({
        type:"post",
        url: "handler/add.php",
       data: serializeData
    });

    request.done(function(response, textStatus, jqXHR){
      if (response === "Success") {
        alert("Tim je dodat")
        location.reload(true)
        //izmeniti da dodaje red bez reload
      }
      else{
    console.log("Tim nije dodat> "+response)
      }
      request.fail(function (jqXHR, textStatus, errorThrown) {
        console.error("Sledeca greska se desila: " + textStatus, errorThrown);
        console.log(jqXHR);
      })
})
  });
////////////////////////
$('#btnDodaj').submit(function () {
  $('myModal').modal('toggle');
  return false;
});

$('#btn-izmeni').submit(function () {

  $('#myModal').modal('toggle');
  return false;
});

