import { messaging, getToken } from "./firebase.js";

var focoEnSelect = false;

var valorAnterior = "";
document.getElementById('telefono').addEventListener("input", function (e) {
    var valorActual = e.target.value;
    var res = parseInt(valorActual);

    if (isNaN(res) && valorActual != "") {
        this.value = valorAnterior
    }
    else {
        this.value = valorActual
        valorAnterior = valorActual
    }
})

document.getElementById("combo").addEventListener("change", function (_) {
    // Ocultar todos los formularios
    document.querySelectorAll('#modificar > div').forEach(div => {
        div.style.display = 'none';
    });
    // Mostrar el formulario seleccionado
    var aCambiar = document.getElementById(this.value);
    if (aCambiar) {
        aCambiar.style.display = 'block';
    }
});
document.querySelector("#datosPersonales > form").addEventListener("submit", function (e) {
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
document.querySelector("#direccion > form").addEventListener("submit", function (e) {
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

document.querySelector("#otrosDatos").addEventListener("submit", function (e) {
    e.preventDefault()

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
document.addEventListener("DOMContentLoaded", function () {
    let botones = Array.from(document.querySelectorAll(".botonLateral"));  // Convertir a un array

    botones.forEach(boton => {
        boton.addEventListener("click", function () {
            // Primero, eliminamos el id "yeah" de todos los botones
            botones.forEach(b => b.id = "nope");

            // Asignamos el id "yeah" solo al botón que fue clickeado
            this.id = "yeah";

            let paneles = document.querySelectorAll(".otraParte > div");

            // Ocultar todos los paneles
            paneles.forEach(panel => {
                panel.style.display = "none";
            });

            // Obtener el valor del atributo data-info y mostrar el panel correspondiente
            var aMostrar = this.getAttribute("data-info");

            switch (aMostrar) {
                case "historial": {
                    cargarHistorial()
                    break;
                }
                case "visualizar": {
                    cargarHoy()
                    break;
                }
                case "cerrarSes": {
                    var a = document.createElement("a")
                    a.setAttribute("href", "./")
                    a.click()
                }
            }

            let panelAMostrar = document.getElementById(aMostrar);
            panelAMostrar.style.display = "block";
        });
    });
    cargarEquipo();
});
setInterval(() => {
    fetch("usuarioValidado.php",{
        method:"POST"
    })
    .then(response => response.text())
    .then(data => {
        if(data != "1"){
            window.location.reload()
        }
    })
}, 3000);
function cargarEquipo() {
    fetch("datosEquipo.php")
        .then(response => response.text())
        .then(data => {
            document.querySelector(".equipo").innerHTML = data
            var boton = document.querySelector(".invitar > button")
            var botonSalir = document.querySelector(".abandonarEquipo")
            if (botonSalir) {
                botonSalir.addEventListener("click", function () {
                    fetch("salirEquipo.php")
                        .then(response => response.text())
                        .then(data => console.log(data))
                })
                var correo;

                var select = document.querySelector(".equipo select")
                if (focoEnSelect) {
                    select.dispatchEvent(new Event("focus"))
                }
                if (select) {
                    select.addEventListener("change", function () {
                        var cant = document.querySelectorAll(".equipo .participante").length
                        var tipo = document.querySelector("span.nombreEquipo").getAttribute("data-tipo")
                        var max = tipo == "duos" ? 2 : 4
                        boton.disabled = this.value == "def" || cant == max;
                        boton.title = this.value == "def" ? "selecciona un participante para invitarle al equipo" : "";
                        boton.title = cant == max ? `ya no puedes añadir mas participantes a tu equipo por el tipo de este (${tipo})` : ""
                        correo = this.value;
                    })
                    document.querySelector(".invitar select").addEventListener("focus", function () {
                        focoEnSelect = true
                    })
                    document.querySelector(".invitar select").addEventListener("blur", function () {
                        focoEnSelect = false
                    })
                }
                if (boton) {
                    boton.addEventListener("click", function () {
                        var equipo = document.querySelector("span.nombreEquipo").innerHTML
                        fetch("invitarParticipante.php", {
                            method: "POST",
                            body: JSON.stringify({
                                equipo: equipo,
                                usuario: correo
                            })
                        })
                            .then(response => response.text())
                            .then(data => {
                                alert(data)
                                select.value = "def"
                                cargarEquipo()
                                select.dispatchEvent(new Event("change"))
                            })
                            .catch(_ => console.error("hubo un error al invitar al participante"))
                    })
                }
                var btnEliminar = document.querySelectorAll(".participante > img")
                if (btnEliminar.length != 0) {
                    btnEliminar.forEach(img => {
                        img.addEventListener("click", function () {
                            var correo = img.parentElement.querySelector("p").title;
                            fetch(`salirEquipo.php?correo=${correo}`)
                                .then(alert("participante eliminado correctamente"))
                        })
                    })
                }
            }
            else {
                var ofertas = document.querySelector(".solicitudes > img")
                if (ofertas) {
                    ofertas.addEventListener("click", function () {
                        var divOfertas = document.querySelector(".ofertas")
                        divOfertas.style.display = divOfertas.style.display == "none" ? "block" : "none"
                    })
                }
                document.querySelectorAll(".ofertas button.aceptar").forEach(boton => {
                    boton.addEventListener("click", function () {
                        var equipo = this.parentElement.querySelector("p").innerHTML
                        fetch(`aceptarEquipo.php?equipo=${equipo}`)
                            .then(response => response.text())
                            .then(data => {
                                console.log(data)
                                cargarEquipo();
                            })
                            .catch(_ => console.error("hubo un fallo al aceptar la oferta"))
                    })
                })
                var botonesRechazar = document.querySelectorAll(".ofertas button.rechazar")
                if (botonesRechazar.length != 0) {
                    botonesRechazar.forEach(boton => {
                        boton.addEventListener("click", function () {
                            var equipo = this.parentElement.querySelector("p").innerHTML
                            fetch(`rechazarOferta.php?equipo=${equipo}`)
                                .then(alert("oferta rechazada correctamente"))
                        })
                    })
                }
                var crear = document.querySelector("span.enlace")
                crear.addEventListener("click", function () {
                    document.querySelector(".crearEquipo").style.display = "block"
                })
                document.querySelector(".crearEquipo > img").addEventListener("click", function () {
                    this.parentElement.style.display = "none"
                })
                var crearEquipo = document.querySelector(".crearEquipo")
                if (crearEquipo) {
                    crearEquipo.querySelector("input").addEventListener("input", function () {
                        var nombre = this.value
                        fetch(`comprobarEquipo.php?equipo=${nombre}`)
                            .then(response => response.text())
                            .then(data => {
                                // console.log(data)
                                var opcion = crearEquipo.querySelector("select").value;
                                if (opcion != "def" && this.value != "" && data == "false") {
                                    crearEquipo.querySelector('input[type="submit"]').disabled = false;
                                }
                                else {
                                    crearEquipo.querySelector('input[type="submit"]').disabled = true;
                                }
                                if (data == "true") {
                                    crearEquipo.querySelector(".mensaje").innerHTML = "ese nombre ya pertenece a un equipo"
                                }
                                else {
                                    crearEquipo.querySelector(".mensaje").innerHTML = ""
                                }
                            })
                    })
                    crearEquipo.querySelector("select").addEventListener("input", function () {
                        var nombre = crearEquipo.querySelector('input').value;
                        var contenido = crearEquipo.querySelector("p.mensaje").innerHTML;
                        if (nombre != "" && this.value != "def" && contenido == "") {
                            crearEquipo.querySelector('input[type="submit"]').disabled = false;
                        }
                        else {
                            crearEquipo.querySelector('input[type="submit"]').disabled = true;
                        }
                    })
                    crearEquipo.querySelector('input[type="submit"]').addEventListener('click', function (e) {
                        e.preventDefault();
                        var nombre = crearEquipo.querySelector("input").value
                        var tipo = crearEquipo.querySelector("select").value
                        fetch("crearEquipo.php", {
                            method: "POST",
                            body: JSON.stringify({
                                nombre: nombre,
                                tipo: tipo
                            })
                        })
                            .then(response => response.text())
                            .then(data => {
                                alert(data)
                                this.parentElement.parentElement.style.display = "none"
                                cargarEquipo();
                            })
                            .catch(error => console.error(`hubo un error al crear el equipo: ${error}`))
                    })
                }
            }
        })
        .catch(error => console.error("hubo un error al sacar los datos del equipo: " + error))
}
setInterval(() => {
    var aInvitar = document.querySelector(".invitar select")

    if(aInvitar == null){
        fetch("ofertasParticipante.php")
            .then(response => response.text())
            .then(data => {
                var cantActual = document.querySelectorAll(".solicitudes .oferta").length
                var cant = parseInt(cantActual)
                if (cant != null && cant != parseInt(data)) {
                    var notificacion = JSON.stringify({
                        titulo: "tienes ofertas para unirte a equipos",
                        body: `${data} equipo/s quieren contar contigo en su equipo, haz click aqui para ver más detalles`,
                        link: "http://localhost/proyectoPesca/cliente.php"
                    })
                    fetch("mandarNotificacion.php", {
                        method: "POST",
                        body: JSON.stringify({
                            notificacion: notificacion
                        })
                    })
                        .then(response => response.text())
                        .then(data => console.log(data))
                        .catch(error => console.error(error))
                    cargarEquipo()
                }
            })
            .catch(_ => console.error("hubo un error"))
    }
}, 3000)
setInterval(() => {

    if (focoEnSelect) {
        document.querySelector(".invitar select").dispatchEvent(new Event("focus"))
    }
    var equipo = document.querySelector("span.nombreEquipo")
    if (equipo) {
        var nombre = equipo.innerHTML;
        fetch(`numeroMiembros.php?equipo=${nombre}`)
            .then(response => response.text())
            .then(data => {
                var participantes = document.querySelectorAll(".participantes > .participante")
                if (participantes.length != parseInt(data)) {
                    var correos = document.querySelectorAll(".participante > p")
                    var titulo = participantes.length > parseInt(data) ? "se ha ido un compañero" : "nuevo compañero"
                    var body = participantes.length > parseInt(data) ? "un compañero ya no forma parte del equipo" : "un jugador que habiais invitado acaba de aceptar la solicitud, ¡dale la bienvenida!"
                    var link = "http://localhost/proyectoPesca/"

                    var notificacion = JSON.stringify({
                        titulo: titulo,
                        body: body,
                        link: link
                    })
                    correos.forEach(correo => {
                        fetch("mandarNotificacion.php", {
                            method: "POST",
                            body: JSON.stringify({
                                notificacion: notificacion,
                                destinatario: correo.title
                            })
                        })
                            .then(response => response.text())
                            .then(data => console.log(data))
                            .catch(error => console.error(error))
                    })
                    cargarEquipo()
                }
            })
    }
}, 3000) //3s
var campeonatoSeleccionado;
var turnoActual;
var aRotarCamp;
var tallaCamp;
var angulo = false
window.rotar = function (aRotar, camp, talla) {
    aRotarCamp = aRotar
    tallaCamp = talla;
    aRotar.style.transform = angulo ? "rotate(0deg)" : "rotate(90deg)"
    angulo = !angulo
    var datos = aRotar.parentElement.querySelector(".datos")
    var estado = aRotar.parentElement.querySelector("div").querySelector("div")
    campeonatoSeleccionado = angulo ? camp : null
    fetch(`cargarDatos.php?campeonato=${camp}&talla=${talla}&estado=${estado.className}`)
        .then(response => response.text())
        .then(data => {
            datos.innerHTML = data
            var turno = document.querySelector(".turno-actual");
            if (turno) {
                turnoActual = parseInt(turno.querySelector(".turno").innerHTML.split("</strong>")[1])
                turno.addEventListener("click", function () { 
                    var cargo = this.querySelector("li.cargo") 
                    if (cargo.innerHTML == "<strong>Cargo:</strong> control") {
                        var modal = document.querySelector(".modalAñadirPieza")
                        modal.style.display = "flex"
                        modal.setAttribute("data-idTurno", this.querySelector(".turno").getAttribute("data-id"))
                        modal.setAttribute("data-idParticipante", this.querySelector(".pareja").getAttribute("data-id"))
                    }
                })
            }
        })
        .catch(error => console.error("hubo un error al conectar con el servidor: " + error))
    datos.style.display = angulo ? "block" : "none"
}
document.querySelector(".modalAñadirPieza > img").addEventListener("click", function () {
    this.parentElement.style.display = "none"
})
window.clickGeneral = function (div) {
    div.querySelector("#girar").dispatchEvent(new Event("click"))
}
setInterval(() => {
    if (campeonatoSeleccionado != null) {
        if (turnoActual != null) {
            fetch(`turnoActual.php?campeonato=${campeonatoSeleccionado}`)
                .then(response => response.text())
                .then(data => {
                    var turnoCamp = parseInt(data)
                    if (turnoCamp != turnoActual) {
                        var datos = aRotarCamp.parentElement.querySelector(".datos")
                        var estado = aRotarCamp.parentElement.querySelector("div").querySelector("div")
                        campeonatoSeleccionado = angulo ? campeonatoSeleccionado : null
                        fetch(`cargarDatos.php?campeonato=${campeonatoSeleccionado}&talla=${tallaCamp}&estado=${estado.className}`)
                            .then(response => response.text())
                            .then(data => {
                                datos.innerHTML = data
                                var turno = document.querySelector(".turno-actual");
                                if (turno) {
                                    turnoActual = parseInt(turno.querySelector(".turno").innerHTML.split("</strong>")[1])
                                    turno.addEventListener("click", function () {
                                        var cargo = this.querySelector("li.cargo")
                                        if (cargo.innerHTML == "<strong>Cargo:</strong> control") {
                                            var modal = document.querySelector(".modalAñadirPieza")
                                            modal.style.display = "flex"
                                            modal.setAttribute("data-idTurno", this.querySelector(".turno").getAttribute("data-id"))
                                            modal.setAttribute("data-idParticipante", this.querySelector(".pareja").getAttribute("data-id"))
                                        }
                                    })
                                }
                            })
                            .catch(error => console.error("hubo un error al conectar con el servidor: " + error))
                    }
                })
            fetch(`piezasPorParticipante.php?campeonato=${campeonatoSeleccionado}`)
                .then(response => response.text())
                .then(data => {
                    var actual = aRotarCamp.parentElement.querySelectorAll("ul > li");
                    if (data != actual.length) {
                        var datos = aRotarCamp.parentElement.querySelector(".datos")
                        var estado = aRotarCamp.parentElement.querySelector("div").querySelector("div")
                        campeonatoSeleccionado = angulo ? campeonatoSeleccionado : null
                        fetch(`cargarDatos.php?campeonato=${campeonatoSeleccionado}&talla=${tallaCamp}&estado=${estado.className}`)
                            .then(response => response.text())
                            .then(data => {
                                datos.innerHTML = data
                                var turno = document.querySelector(".turno-actual");
                                if (turno) {
                                    turnoActual = parseInt(turno.querySelector(".turno").innerHTML.split("</strong>")[1])
                                    turno.addEventListener("click", function () {
                                        var cargo = this.querySelector("li.cargo")
                                        if (cargo.innerHTML == "<strong>Cargo:</strong> control") {
                                            var modal = document.querySelector(".modalAñadirPieza")
                                            modal.style.display = "flex"
                                            modal.setAttribute("data-idTurno", this.querySelector(".turno").getAttribute("data-id"))
                                            modal.setAttribute("data-idParticipante", this.querySelector(".pareja").getAttribute("data-id"))
                                        }
                                    })
                                }
                            })
                            .catch(error => console.error("hubo un error al conectar con el servidor: " + error))
                    }
                })
        }
    }
}, 3000)
function cargarHistorial() {
    var contenedor = document.querySelector("#historial")
    contenedor.innerHTML = "<p>no estas apuntado en ningun torneo por ahora</p>"
    fetch("sacarHistorial.php")
        .then(response => response.text())
        .then(data => {
            if (data.startsWith("<")) {
                contenedor.innerHTML = data
            }
        })
        .catch(_ => console.error("error al conectar con el servidor"))
}

window.apuntarCampeonato = function (id_campeonato, correo) {
    var json = `
    {
        "id_campeonato":${id_campeonato},
        "correo":"${correo}"
    }
    `
    fetch("apuntarCampeonato.php", {
        method: "POST",
        body: json
    })
        .then(response => response.text())
        .then(_ => cargarHoy())
        .catch(_ => console.log("hubo un error al conectar con el servidor"))
}

window.cancelarSuscripcion = function (id_campeonato, correo) {
    var json = `
    {
        "id_campeonato":${id_campeonato},
        "correo":"${correo}"
    }
    `
    fetch("cancelarSuscripcion.php", {
        method: "POST",
        body: json
    })
        .then(response => response.text())
        .then(_ => cargarHoy())
        .catch(_ => console.error("Hubo un problema al conectar con el servidor"))
}

function cargarHoy() {
    var contenedor = document.querySelector("#visualizar")

    fetch("sacarHoy.php")
        .then(response => response.text())
        .then(data => contenedor.innerHTML = data)
        .catch(_ => console.error("error al conectar con el servidor"))
}
document.getElementById("notis").addEventListener("input", function () {
    if (this.checked) {
        Notification.requestPermission(function (permission) {
            if (permission == "granted") {
                console.log("el usuario ha permitido las notificaciones")
            }
            else {
                console.log("el usuario ha denegado las notificaciones")
            }
        })
    }
})
document.querySelector(".modalAñadirPieza > form").addEventListener("submit", function (e) {
    e.preventDefault();
    var tamaño = this.querySelector("#tamañoPieza");
    var participante = this.parentElement.getAttribute("data-idTurno")
    var turno = this.parentElement.getAttribute("data-idParticipante")

    var formData = new FormData();
    formData.append("tamaño", tamaño.value)
    formData.append("turno", participante)
    formData.append("participante", turno)

    fetch("añadirPieza.php", {
        method: "POST",
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            console.log(data)
            document.querySelector(".girar").dispatchEvent(new Event("click"))
            this.parentElement.style.display = "none"
        })
        .catch(error => console.error("hubo un fallo al añadir la pieza:" + error))
    this.reset()
})
document.querySelector(".modalAñadirPieza input#tamañoPieza").addEventListener("input", function () {
    document.querySelector(".modalAñadirPieza input#add").disabled = this.value == "";
})
