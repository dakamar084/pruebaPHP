
var nombreCamp;
var tipo;
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
            tipo = e.target.getAttribute("data-tipo")
            nombreCamp = e.target.innerHTML;
            fetch("datosCampeonato.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ campeonato, categoria, tipo })
            })
                .then(response => response.text())
                .then(data => {
                    modal.innerHTML = data;
                    tipo = e.target.getAttribute("data-tipo")
                    return fetch(`sacarParticipantes.php?campeonato=${campeonato}&tipo=${tipo}`);
                })
                .then(response => response.text())
                .then(data => {
                    var sorteado = document.querySelector(".accion").getAttribute("data-sorteado")
                    document.querySelector("#modal > .participantes").innerHTML = data ?? "<p>no hay participantes actualmente</p>";
                    var ele = document.querySelectorAll(".participantes img")

                    ele.forEach(ele => {
                        ele.style.display = sorteado == "true" ? "none" : "block"
                    })
                    if (tipo == "individual") {
                        document.querySelector(".accion").innerHTML = document.querySelectorAll(".participantes div").length % 2 == 0 && document.querySelectorAll(".participantes div").length ? '<button>sorteo</button>  <button class="siguiente" disabled title="debes realizar el sorteo para continuar">Siguiente turno</button>' : "<p>debes añadir un numero par mayor que 0 de participantes para continuar</p>"
                    }
                    else {
                        document.querySelector(".accion").innerHTML = document.querySelectorAll(".participantes div").length > 1 ? '<button>sorteo</button>  <button class="siguiente" disabled title="debes realizar el sorteo para continuar">Siguiente turno</button>' : "<p>debes añadir un numero par mayor que 0 de participantes para continuar</p>"
                    }
                    var boton = document.querySelector(".accion > button")
                    if (boton != null) {
                        document.querySelector(".accion > .siguiente").disabled = sorteado != "true";
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
                                    mandarCorreo(campeonato)
                                    var sorteado = document.querySelector(".accion").getAttribute("data-sorteado")
                                    document.querySelector(".accion > .siguiente").disabled = false;
                                    ele.forEach(ele => {
                                        ele.style.display = sorteado == "true" ? "none" : "block"
                                    })
                                    fetch(`repartoParejas.php?campeonato=${campeonato}`)
                                        .then(response => response.text())
                                        .then(data => console.log(data))
                                        .catch(error => console.error("hubo un error al repartir las parejas: " + error))

                                    document.querySelector("#modal > button").title = "no puedes añadir participantes una vez se realiza el sorteo"
                                })
                                .catch(error => console.log("hubo un error al generar los roles: " + error))
                            console.log(campeonato ?? "es nulo")

                        })
                    }
                    var otroBoton = document.querySelector(".accion .siguiente")
                    if (otroBoton) {
                        otroBoton.addEventListener("click", function () {
                            fetch(`subirTurno.php?campeonato=${campeonato}`)
                                .then(response => response.text())
                                .then(data => {
                                    console.log(data)
                                    document.querySelector(".turno").innerHTML = parseInt(document.querySelector(".turno").innerHTML) + 1;
                                    var notificacion = JSON.stringify({
                                        titulo: `cambio de turno en ${nombreCamp}`,
                                        body: `el siguiente turno de ${nombreCamp} empieza en 10 minutos, haz click aqui para mas información`,
                                        link: "http://localhost/proyectoPesca"
                                    })
                                    var participantes = document.querySelectorAll(".participantes p")
                                    participantes.forEach(participante => {
                                        fetch("mandarNotificacion.php", {
                                            headers: {
                                                "Content-Type": "application/json"
                                            },
                                            method: "POST",
                                            body: JSON.stringify({
                                                notificacion: notificacion,
                                                destinatario: participante.innerHTML.trim()
                                            })
                                        })
                                            .then(response => response.text())
                                            .then(data => console.log(data))
                                            .catch(error => console.error("error al mandar la notificacion: " + error))
                                    })
                                })
                                .catch(error => console.error("error al incrementar el turno: " + error))
                        })
                    }
                })
                .catch(error => console.error("Hubo un problema al conectar con el servidor: " + error));

            modal.style.display = "block";
        }
    });
});
setInterval(() => {
    fetch("cantidadTorneos.php")
        .then(response => response.text())
        .then(data => {
            var cantidadTorneos = document.querySelectorAll("button.torneo").length ?? 1;
            var cant = parseInt(data)
            if (cant != cantidadTorneos) {
                document.dispatchEvent(new Event("DOMContentLoaded"))
            }
        })
}, 3000)

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

        fetch("apuntarCampeonato.php", {
            method: "POST",
            body: JSON.stringify({
                id_campeonato: campeonato,
                correo: correo
            })
        })
            .then(response => response.text())
            .then(data => console.log(data))
            .catch(error => console.error("hubo un error: " + error))

        fetch("añadirParticipante.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ correo, campeonato, tipo })
        })
            .then(response => response.text())
            .then(data => {
                alert(data);
                return fetch(`sacarParticipantes.php?campeonato=${campeonato}&tipo=${tipo}`);
            })
            .then(response => response.text())
            .then(data => {
                document.querySelector("#modal > .participantes").innerHTML = data ?? "<p>no hay participantes actualmente</p>";
                var ele = document.querySelectorAll(".participantes img")
                var sorteado = document.querySelector(".accion").getAttribute("data-sorteado")
                ele.forEach(ele => {
                    ele.style.display = sorteado == "true" ? "none" : "block"
                })
                if (tipo == "individual") {
                    document.querySelector(".accion").innerHTML = document.querySelectorAll(".participantes div").length % 2 == 0 && document.querySelectorAll(".participantes div").length ? '<button>sorteo</button>  <button class="siguiente" disabled title="debes realizar el sorteo para continuar">Siguiente turno</button>' : "<p>debes añadir un numero par mayor que 0 de participantes para continuar</p>"
                }
                else {
                    document.querySelector(".accion").innerHTML = document.querySelectorAll(".participantes div").length > 1 ? '<button>sorteo</button>  <button class="siguiente" disabled title="debes realizar el sorteo para continuar">Siguiente turno</button>' : "<p>debes añadir mas de un equipo para realizar el sorteo de roles</p>"
                }
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
                                mandarCorreo(campeonato)
                                document.querySelector("#modal > button").title = "no puedes añadir participantes una vez se realiza el sorteo"
                                var ele = document.querySelectorAll(".participantes img")
                                document.querySelector(".accion > .siguiente").disabled = false;
                                fetch(`repartoParejas.php?campeonato=${campeonato}`)
                                    .then(response => response.text())
                                    .then(data => console.log(data))
                                    .catch(error => console.error("hubo un error al repartir las parejas: " + error))
                                ele.forEach(ele => {
                                    ele.style.display = "none"
                                })
                            })
                            .catch(error => console.log("hubo un error al generar los roles: " + error))
                        console.log(campeonato)
                    })
                }
                var otroBoton = document.querySelector(".accion .siguiente")
                if (otroBoton) {
                    otroBoton.addEventListener("click", function () {
                        console.log(campeonato ?? "es nulo")
                        fetch(`subirTurno.php?campeonato=${campeonato}`)
                            .then(response => response.text())
                            .then(data => {
                                console.log(data)
                                document.querySelector(".turno").innerHTML = parseInt(document.querySelector(".turno").innerHTML) + 1;
                                var notificacion = JSON.stringify({
                                    titulo: `cambio de turno en ${nombreCamp}`,
                                    body: `el turno de ${nombreCamp} se ha acabado, haz click aqui para mas información`,
                                    link: "http://localhost/proyectoPesca"
                                })
                                var participantes = document.querySelectorAll(".participantes p")
                                participantes.forEach(participante => {
                                    fetch("mandarNotificacion.php", {
                                        headers: {
                                            "Content-Type": "application/json"
                                        },
                                        method: "POST",
                                        body: JSON.stringify({
                                            notificacion: notificacion,
                                            destinatario: participante.innerHTML.trim()
                                        })
                                    })
                                        .then(response => response.text())
                                        .then(data => console.log(data))
                                        .catch(error => console.error("error al mandar la notificacion: " + error))
                                })
                            })
                            .catch(error => console.error("error al incrementar el turno: " + error))
                    })
                }
                    var notificacion = JSON.stringify({
                    titulo: `${correo}, te han apuntado en un nuevo campeonato`,
                    body: `has sido inscrito en un nuevo campeonato, para mas informacion haz click aqui`,
                    link: "http://localhost/proyectoPesca/"
                })
                fetch('mandarNotificacion.php', {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        notificacion: notificacion,
                        destinatario: correo
                    })
                })
                    .then(response => response.text())
                    .then(data => console.log(data))
                    .catch(error => console.log("error al mandar la notificacion: " + error))
            })
            .catch(_ => console.log("Hubo un fallo al conectar con el servidor"));
    }
    else if (e.target.matches("#modal > .participantes > .participante > img")) {
        var correo = e.target.getAttribute("data-correo")
        var campeonato = e.target.getAttribute("data-campeonato")

        fetch("cancelarSuscripcion.php", {
            method: "POST",
            body: JSON.stringify({
                id_campeonato: campeonato,
                correo: correo
            })
        })

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

                var notificacion = JSON.stringify({
                    titulo: `${correo}, tienes nueva actividad`,
                    body: `te han eliminado de un torneo`,
                    link: "http://localhost/proyectoPesca/"
                })
                fetch("mandarNotificacion.php", {
                    method: "POST",
                    body: JSON.stringify({
                        notificacion: notificacion,
                        destinatario: correo
                    })
                })
                    .then(response => response.text())
                    .then(data => console.log(data))
                    .catch(error => console.error("hubo un error al notificar al usuario: " + error))
                return fetch(`sacarParticipantes.php?campeonato=${campeonato}&tipo=${tipo}`)
            })
            .then(response => response.text())
            .then(data => {
                document.querySelector("#modal > .participantes").innerHTML = data ?? "no hay participantes de momento"
                var ele = document.querySelectorAll(".participantes img")

                var sorteado = document.querySelector(".accion").getAttribute("data-sorteado")

                ele.forEach(ele => {
                    ele.style.display = sorteado == "true" ? "none" : "block"
                })
                if (tipo == "individual") {
                    document.querySelector(".accion").innerHTML = document.querySelectorAll(".participantes div").length % 2 == 0 && document.querySelectorAll(".participantes div").length ? '<button>sorteo</button>  <button class="siguiente" disabled title="debes realizar el sorteo para continuar">Siguiente turno</button>' : "<p>debes añadir un numero par mayor que 0 de participantes para continuar</p>"
                }
                else {
                    document.querySelector(".accion").innerHTML = document.querySelectorAll(".participantes div").length > 1 ? '<button>sorteo</button>  <button class="siguiente" disabled title="debes realizar el sorteo para continuar">Siguiente turno</button>' : "<p>debes añadir mas de un equipo para continuar</p>"
                }
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
                                mandarCorreo(campeonato)
                                var ele = document.querySelectorAll(".participantes img")
                                var sorteado = document.querySelector(".accion").getAttribute("data-sorteado")

                                ele.forEach(ele => {
                                    ele.style.display = sorteado == "true" ? "none" : "block"
                                })

                                fetch(`repartoParejas.php?campeonato=${campeonato}`)
                                    .then(response => response.text())
                                    .then(data => console.log(data))
                                    .catch(error => console.error("hubo un error al repartir las parejas: " + error))
                                document.querySelector("#modal > button").title = "no puedes añadir participantes una vez se realiza el sorteo"
                                document.querySelector(".accion > .siguiente").disabled = false;
                            })
                            .catch(error => console.log("hubo un error al generar los roles: " + error))
                    })
                }
                var otroBoton = document.querySelector(".accion .siguiente")
                if (otroBoton) {
                    otroBoton.addEventListener("click", function () {
                        fetch(`subirTurno.php?campeonato=${campeonato}`)
                            .then(response => response.text())
                            .then(data => {
                                console.log(data)
                                document.querySelector(".turno").innerHTML = parseInt(document.querySelector(".turno").innerHTML) + 1;
                                var notificacion = JSON.stringify({
                                    titulo: `cambio de turno en ${nombreCamp}`,
                                    body: `el turno de ${nombreCamp} se ha acabado, haz click aqui para mas información`,
                                    link: "http://localhost/proyectoPesca"
                                })
                                var participantes = document.querySelectorAll(".participantes p")
                                participantes.forEach(participante => {
                                    fetch("mandarNotificacion.php", {
                                        headers: {
                                            "Content-Type": "application/json"
                                        },
                                        method: "POST",
                                        body: JSON.stringify({
                                            notificacion: notificacion,
                                            destinatario: participante.innerHTML.trim()
                                        })
                                    })
                                        .then(response => response.text())
                                        .then(data => console.log(data))
                                        .catch(error => console.error("error al mandar la notificacion: " + error))
                                })
                            })
                            .catch(error => console.error("error al incrementar el turno: " + error))
                    })
                }
            })
            .catch(_ => console.error("hubo un fallo al conectar con el servidor"))
    }
});

document.querySelector(".perfil > img").addEventListener("click", function () {
    document.querySelector(".botones").style.display = document.querySelector(".botones").style.display == "none" ? "flex" : "none"
})
document.querySelector(".modalDatos > img").addEventListener("click", function () {
    this.parentElement.style.display = "none"
})
document.querySelector("#modDatos").addEventListener("click", function () {
    document.querySelector(".modalDatos").style.display = "block"
})
document.querySelectorAll(".modalDatos > div").forEach(div => {
    div.style.display = div.className == "personales" ? "block" : "none"
    document.querySelector(".mover").style.display = "block"
})
document.querySelector(".modalDatos select").addEventListener("input", function () {
    document.querySelectorAll(".modalDatos > div").forEach(div => {
        div.style.display = div.className == this.value ? "block" : "none"
        document.querySelector(".mover").style.display = "block"
    })
})
const modal = document.querySelector(".modalDatos");

let isDown = false;
let offsetX, offsetY;

function startDrag(e) {
    if (e.target.matches(".mover")) {
        isDown = true;
        const event = e.type.startsWith('touch') ? e.touches[0] : e;
        offsetX = event.clientX - modal.offsetLeft;
        offsetY = event.clientY - modal.offsetTop;
    }
}

function drag(e) {
    if (isDown) {
        // Evitar que se mueva la pantalla en touch
        if (e.cancelable) {
            e.preventDefault();
        }

        const event = e.type.startsWith('touch') ? e.touches[0] : e;
        const x = event.clientX - offsetX;
        const y = event.clientY - offsetY;
        modal.style.left = x + "px";
        modal.style.top = y + "px";
    }
}

function endDrag() {
    isDown = false;
}

// Eventos para mouse
modal.addEventListener("mousedown", startDrag);
modal.addEventListener("mousemove", drag);
modal.addEventListener("mouseup", endDrag);

// Eventos para touch
modal.addEventListener("touchstart", startDrag);
modal.addEventListener("touchmove", drag, { passive: false });
modal.addEventListener("touchend", endDrag);

document.querySelector(".personales > form").addEventListener("submit", function (e) {
    e.preventDefault();
    var nombre = document.querySelector('input[name="nombre"]')
    var correo = document.querySelector('input[name="correo"]')
    var telefono = document.querySelector('input[name="telefono"]')
    var fechaNac = document.querySelector('input[name="fecha_nacimiento"]')

    var json = `
    {
        "nombre":"${nombre.value}",
        "correo":"${correo.value}",
        "telefono":"${telefono.value}",
        "fechaNac":"${fechaNac.value}"
    }
    `

    fetch("modificarPersonal.php", {
        method: "POST",
        body: json
    })
        .then(response => response.text())
        .then(data => alert(data))
        .catch(_ => console.log("hubo un error al conectar con el servidor"))
})
document.querySelector(".direccion > form").addEventListener("submit", function (e) {
    e.preventDefault();
    var direccion = document.querySelector('input[name="direccion"]')
    var cp = document.querySelector('input[name="cp"]')
    var provincia = document.querySelector('input[name="provincia"]')
    var pais = document.querySelector('input[name="pais"]')

    var json = `
    {
        "direccion":"${direccion.value}",
        "cp":"${cp.value}",
        "provincia":"${provincia.value}",
        "pais":"${pais.value}"
    }
    `

    fetch("modificarDireccion.php", {
        method: "POST",
        body: json
    })
        .then(response => response.text())
        .then(data => alert(data))
        .catch(_ => console.log("hubo un error al conectar con el servidor"))
})
document.querySelector("otros > form", function () {

    var formData = new FormData();

    var numLicencia = document.querySelector('input[name="licencia"]')
    var NumFede = document.querySelector('input[name="federativa"]')
    let permisosSeleccionados = document.querySelectorAll('input[name="permisos[]"]');

    formData.append("licencia", numLicencia.value)
    formData.append("fede", NumFede.value)

    var algunoSeleccionado = false
    permisosSeleccionados.forEach(permiso => {
        formData.append("permisos[]", permiso.checked ? 1 : 0)
        if (permiso.checked) {
            algunoSeleccionado = true;
        }
    });
    navigator.serviceWorker.register("./firebase-messaging-sw.js")
        .then((registration) => {
            return getToken(messaging, {
                vapidKey: "BOg_i-Wk1kRfqJgifQJIfgdbDOtsRkekD-AaBR2Y9-d-17kQNXNGwyODkVKlAKESVoAKpEfu0GOPbvudR_-y-5U",
                serviceWorkerRegistration: registration
            });
        })
        .then((token) => {
            formData.append("endpoint", algunoSeleccionado ? token : null)
            fetch("modificarOtros.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.text())
                .then(data => console.log(data))
                .catch(_ => console.error("error al conectar con el servidor"))
        })
})
document.querySelector("#cerrarSesion").addEventListener("click", function (e) {
    e.preventDefault()
    var a = document.createElement("a");
    a.setAttribute("href", "./")
    a.click()
})
function mandarCorreo(campeonato) {
    var correos = document.querySelectorAll("div.participante > p")
    var formData = new FormData(); var correos = document.querySelectorAll("div.participante > p")

    correos.forEach(correo => {
        formData.append("correos[]", correo.getAttribute("data-correo"))
    })
    formData.append("asunto", "reparto de roles")
    formData.append("mensaje", `<html><head></head><body background-color:aliceblue; text-shadow:1px 5px 3px gray;><h1>Ya se han generado los roles del torneo ${nombreCamp}</h1><p>Para mas información, clicka <a href="localhost/proyectoPesca/">aqui</a> <p style="font-style:italic;">Este correo se ha generado automaticamente, por favor no responder</p></body></html>`)
    formData.append("campeonato", campeonato)

    fetch("mandarCorreo.php", {
        method: "POST",
        body: formData
    })
        .then(response => response.text())
        .then(data => console.log(data))
        .catch(error => console.error("Hubo un error al enviar el correo a los participantes" + error))
}