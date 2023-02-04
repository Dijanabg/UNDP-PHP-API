
//PRIKAZI SVE

$("#prikaziSve").submit(function (e) {
    
    // da sprecimo refresh stranice nakon submita forme
    e.preventDefault();
    //provera da li je prikazi sve zapoceto
    
    //clg
    console.log("Prikazi sve zapoceto...")
    //request je tipa jqXHR - jquery XmlHttpRequest - povratna vrednost funkcije ajax
    request=$.ajax({
        type:"get",
        url: "http://localhost/undp8/ajax/taskapi/tasks",
        //da nismo koristili veb servise url:"../nesto/nesto.php"
       
    });
    request.done(function(response, textStatus,jqXHR){
        rezultat=response.data.tasks
        console.log(rezultat)
        //isprazni sve pre prikaza taskova
        $("#myTable tbody").empty();
        //prikaz taskova
        for(let i =0; i<rezultat.length;i++){
            //```` pored jedinice s leve strane
            dodajRed(rezultat[i])
        }
    })
    request.fail(function(jqXHR, textStatus,errorThrown){
        console.log("Desila se sledeca greska: " + textStatus, errorThrown)
    })
});

///DODAVANJE NOVOG TASKA

$("#inserttask").click(function (e) { 
    e.preventDefault();
    console.log("Dodaj novi task zapoceto...")

    const $form=$(this).closest("form")
    // console.log($form);

    // let obj = $form.serializeArray();
    // console.log(obj)

    let objekat =$form.serializeArray().reduce(function(json, {name,value}){
    json[name]=value
    return json
    }, {})
    console.log(objekat)

    const objekatJSON = JSON.stringify(objekat)
    console.log(objekatJSON)

    request=$.ajax({
        contentType: "application/json",
        type:"post",
        url: "http://localhost/undp8/ajax/taskapi/tasks",
        //da nismo koristili veb servise url:"../nesto/nesto.php"
       data: objekatJSON
    });
    request.done(function(response, textStatus, jqXHR){
        console.log(response)
        rezultat=response.data.task
        console.log(rezultat)
        dodajRed(rezultat)
        $form[0].reset() //da se resetuje nakon dodavanja novog taska
    })
    request.fail(function(jqXHR, textStatus,errorThrown){
        console.log("Desila se sledeca greska: " + textStatus, errorThrown)
        console.log(jqXHR)
    })
});

//DELETE
$("#obrisi").click(function (e) {
    e.preventDefault()
    console.log("Obrisi je pokrenuto.")
  
    // selektovanje označenog taska putem radio-buttona
    const checkedInput = $("input[type=radio]:checked")
    // vrednost inputa je zapravo id taska
    console.log(checkedInput.val())
  
    request = $.ajax({
      url: "http://localhost/undp8/ajax/taskapi/tasks/" + checkedInput.val(),
      type: "delete"
    })
  
    request.done(function (response, textStatus, jqXHR) {
      console.log(response.messages)
  alert(response.messages[0])
      // ukloniti najbliži red čekiranom input polju (iz tabele sa fronta skloniti obrisani task)
      checkedInput.closest("tr").remove()//front brisanje iz tabele
    })
  
    request.fail(function (jqXHR, textStatus, errorThrown) {
      console.error("Sledeca greska se desila: " + textStatus, errorThrown)
      console.log(jqXHR)
    })
  })

// 4. Azuriranje patch/update
//4.1 popuni formu sa vrednostima selektovanog taska
$("#izmeni").click(function (e) {
    e.preventDefault()
    
    const task = $("input[type=radio]:checked")
    var taskid = task.val()

    console.log("Izmeni je pokrenuto....")
    console.log("taks za azuriranje> "+taskid)

    $.getJSON("http://localhost/undp8/ajax/taskapi/tasks/"+taskid,
    function (response) {
        $("#taskid").val(taskid)
        $("#taskid").show()
        $("#inserttask").hide()
        $("#updatetask").show()

        console.log("Odgovor iz get JSON")
        console.log(response)

        $("#title").val(response.data.tasks[0].title)
        $("#description").val(response.data.tasks[0].description)
        $("#completed").val(response.data.tasks[0].completed)
        
    })
});
$("#updatetask").click(function (e) { 
    e.preventDefault();
    console.log("Updating task...")

    const $form = $(this).closest("form")
    console.log($form)

    let obj = $form.serializeArray()
    console.log(obj)
    //u slucaju da imamo disabled a ne readonly dobije 3 polja samo serilizacijom
    let objekat = obj.reduce(function(json, {name, value}){
        json[name]=value
        return json
    },{})
    objekat.id = $("#taskid").val() //ovo mozemo da zakomentarisemo ako je readonly id
    console.log(objekat)

    const objJSON = JSON.stringify(objekat)

    request=$ajax({
        contentType:"application/json",
        url: "http://localhost/undp8/ajax/taskapi/tasks/"+objekat.id,
        type:"patch",
        data: objJSON,
    })
    request.done(function(res, textStatus, jqXHR){
        console.log(res.data.task[0])
        $form[0].reset()
        $("#taskid").hide()
        $("#inserttask").show()
        $("#updatetask").hide()

        izmeniRed(res.data.task[0])
    })
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.error("Sledeca greska se desila: " + textStatus, errorThrown)
        console.log(jqXHR)
    })
});
    





function izmeniRed(rezultat) {
    const redradio = $("input[type=radio]=checked")
    const red = redradio.closest("tr")
    red.children()[0].textContent = rezultat.title
    red.children()[1].textContent = rezultat.description
    
}

function dodajRed(rezultat) { 
    red=`
            <tr>
                <td>${rezultat.title}</td>
                <td>${rezultat.description}</td>
                <td>
                    <input type="radio" name="taskovi" value="${rezultat.id}">
                </td>
            </tr>`
            $("#myTable tbody").append(red);
 }