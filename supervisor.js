

document.addEventListener("DOMContentLoaded", function () {
    var main = document.getElementById("main");
    fetch("torneosSupervisor.php")
        .then(response => response.text())
        .then(data => main.innerHTML = data)
        .catch(_ => console.error("Hubo un error al conectar con el servidor"));

    main.addEventListener("click", function (e) {
        if (e.target.matches("#main > button")) {
            var modal = document.getElementById("modal");
            var campeonato = e.target.getAttribute("data-id");
            var categoria = e.target.getAttribute("data-categoria");

            fetch("datosCampeonato.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ campeonato, categoria })
            })
                .then(response => response.text())
                .then(data => {
                    modal.innerHTML = data;
                    return fetch(`sacarParticipantes.php?campeonato=${campeonato}`);
                })
                .then(response => response.text())
                .then(data => {
                    var sorteado = document.querySelector(".accion").getAttribute("data-sorteado")

                    document.querySelector("#modal > .participantes").innerHTML = data ?? "no hay participantes actualmente";
                    var ele = document.querySelectorAll(".participantes img")

                    var sorteado = document.querySelector(".accion").getAttribute("data-sorteado")

                    ele.forEach(ele => {
                        ele.style.display = sorteado == "true" ? "none" : "block"
                    })
                    document.querySelector("#modal > .accion").innerHTML = document.querySelectorAll(".participantes div").length % 2 == 0 && document.querySelectorAll(".participantes div").length != 0 ? "<button>sorteo</button>" : "<p>debes añadir un numero par mayor que 0 de participantes para continuar</p>"
                    var boton = document.querySelector(".accion > button")
                    if (boton != null) {
                        boton.disabled = sorteado != "false";
                        boton.title = sorteado != "false" ? "ya has generado el sorteo del campeonato" : "generar el sorteo de roles de los participantes"
                        boton.addEventListener("click", function () {
                            fetch(`generarSorteo.php?campeonato=${campeonato}`)
                                .then(response => response.text())
                                .then(data => {
                                    alert(data)
                                    this.disabled = true
                                    document.querySelector(".accion").setAttribute("data-sorteado", "true")
                                    this.title = "Ya has generado el sorteo de roles"
                                    document.querySelector("#modal > button").disabled = true
                                    var ele = document.querySelectorAll(".participantes img")

                                    var sorteado = document.querySelector(".accion").getAttribute("data-sorteado")

                                    ele.forEach(ele => {
                                        ele.style.display = sorteado == "true" ? "none" : "block"
                                    })

                                    document.querySelector("#modal > button").title = "no puedes añadir participantes una vez se realiza el sorteo"
                                })
                                .catch(error => console.log("hubo un error al generar los roles: " + error))
                        })
                    }
                })
                .catch(_ => console.error("Hubo un problema al conectar con el servidor"));

            modal.style.display = "block";
        }
    });
});

document.getElementById("modal").addEventListener("click", function (e) {
    if (e.target.matches("#modal > img")) {
        this.style.display = "none";
    } else if (e.target.matches("#modal > select")) {
        e.target.addEventListener("input", function () {
            var boton = document.querySelector("#modal > button")
            boton.disabled = (this.value === "def") || (this.value != "del") && (document.querySelector("#modal > .accion").getAttribute("data-sorteado") == 'true');
            if ((this.value != "del") && (document.querySelector("#modal > .accion").getAttribute("data-sorteado") == 'true')) {
                boton.title = "no se pueden añadir participantes una vez realizado el sorteo"
            }
            else {
                boton.setAttribute("title", boton.disabled ? "debes seleccionar un participante para añadirlo a la lista" : `Añadir a ${this.value} a la lista`)
            }
        });
    } else if (e.target.matches("#modal > button")) {
        var correo = document.querySelector("#listaParticipantes").value;
        var campeonato = e.target.getAttribute("data-id");

        fetch("añadirParticipante.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ correo, campeonato })
        })
            .then(response => response.text())
            .then(data => {
                console.log(data);
                return fetch(`sacarParticipantes.php?campeonato=${campeonato}`);
            })
            .then(response => response.text())
            .then(data => {

                document.querySelector("#modal > .participantes").innerHTML = data ?? "<p>no hay participantes actualmente</p>";
                var ele = document.querySelectorAll(".participantes img")
                var sorteado = document.querySelector(".accion").getAttribute("data-sorteado")
                ele.forEach(ele => {
                    ele.style.display = sorteado == "true" ? "none" : "block"
                })
                document.querySelector(".accion").innerHTML = document.querySelectorAll(".participantes div").length % 2 == 0 && document.querySelectorAll(".participantes div").length != 0 ? "<button>sorteo</button>" : "<p>debes añadir un numero par mayor que 0 de participantes para continuar</p>"
                var boton = document.querySelector(".accion > button")
                if (boton != null) {
                    boton.addEventListener("click", function () {
                        fetch(`generarSorteo.php?campeonato=${campeonato}`)
                            .then(response => response.text())
                            .then(data => {
                                alert(data)
                                this.disabled = true
                                document.querySelector(".accion").setAttribute("data-sorteado", "true")
                                this.title = "Ya has generado el sorteo de roles"
                                document.querySelector("#modal > button").disabled = true

                                document.querySelector("#modal > button").title = "no puedes añadir participantes una vez se realiza el sorteo"
                                var ele = document.querySelectorAll(".participantes img")

                                ele.forEach(ele => {
                                    ele.style.display = "none"
                                })
                            })
                            .catch(error => console.log("hubo un error al generar los roles: " + error))
                    })
                }
                var notificacion = JSON.stringify({
                    titulo:`${correo}, te han apuntado en un nuevo campeonato`,
                    body:`has sido inscrito en un nuevo campeonato, para mas informacion haz click aqui`,
                    link:"http://localhost/proyectoPesca/"
                })
                fetch('mandarNotificacion.php',{
                    method:"POST",
                    headers:{
                        "Content-Type":"application/json"
                    },
                    body:JSON.stringify({
                        notificacion:notificacion,
                        destinatario:correo
                    })
                })
                .then(response => response.text())
                .then(data => console.log(data))
                .catch(error=> console.log("error al mandar la notificacion: "+error))
            })
            .catch(_ => console.log("Hubo un fallo al conectar con el servidor"));
    }
    else if (e.target.matches("#modal > .participantes > .participante > img")) {
        var correo = e.target.getAttribute("data-correo")
        var campeonato = e.target.getAttribute("data-campeonato")

        fetch("eliminarParticipante.php", {
            method: "POST",
            body: JSON.stringify({
                campeonato: campeonato,
                correo: correo
            })
        })
            .then(response => response.text())
            .then(data => {
                console.log(data)
                return fetch(`sacarParticipantes.php?campeonato=${campeonato}`)
            })
            .then(response => response.text())
            .then(data => {
                document.querySelector("#modal > .participantes").innerHTML = data ?? "no hay participantes de momento"
                var ele = document.querySelectorAll(".participantes img")

                var sorteado = document.querySelector(".accion").getAttribute("data-sorteado")

                ele.forEach(ele => {
                    ele.style.display = sorteado == "true" ? "none" : "block"
                })
                document.querySelector(".accion").innerHTML = document.querySelectorAll(".participantes div").length % 2 == 0 && document.querySelectorAll(".participantes div").length ? "<button>sorteo</button>" : "<p>debes añadir un numero par mayor que 0 de participantes para continuar</p>"
                var boton = document.querySelector(".accion > button")
                if (boton != null) {
                    boton.addEventListener("click", function () {
                        fetch(`generarSorteo.php?campeonato=${campeonato}`)
                            .then(response => response.text())
                            .then(data => {
                                alert(data)
                                this.disabled = true
                                document.querySelector(".accion").setAttribute("data-sorteado", "true")
                                this.title = "Ya has generado el sorteo de roles"
                                document.querySelector("#modal > button").disabled = true

                                var ele = document.querySelectorAll(".participantes img")

                                var sorteado = document.querySelector(".accion").getAttribute("data-sorteado")

                                ele.forEach(ele => {
                                    ele.style.display = sorteado == "true" ? "none" : "block"
                                })

                                document.querySelector("#modal > button").title = "no puedes añadir participantes una vez se realiza el sorteo"
                            })
                            .catch(error => console.log("hubo un error al generar los roles: " + error))
                    })
                }
            })
            .catch(_ => console.error("hubo un fallo al conectar con el servidor"))
    }
});