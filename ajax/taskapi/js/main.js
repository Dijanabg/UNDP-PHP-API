
//PRIKAZI SVE

$("#prikaziSve").submit(function (e) {
    //provera da li je prikazi sve zapoceto
    e.preventDefault();
    //clg
    console.log("Prikazi sve zapoceto...")
    //request je tipa jqxhr
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

$("#dodaj").submit(function (e) { 
    e.preventDefault();
    console.log("Dodaj novi task zapoceto...")

    const $form=$(this);
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
        rezultat=response.data.task
        console.log(rezultat)
        dodajRed(rezultat)
    })
    request.fail(function(jqXHR, textStatus,errorThrown){
        console.log("Desila se sledeca greska: " + textStatus, errorThrown)
        console.log(jqXHR)
    })
});

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