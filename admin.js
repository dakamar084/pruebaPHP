document.querySelector("#tablaParticipantes").addEventListener("click", function () {
    var contenido = document.querySelector(".otraParte")
    contenido.style.display = "none"

    var participantes = document.querySelector(".tablaParticipantes")

    fetch('cargarParticipantes.php')
        .then(response => response.text())
        .then(data => {
            participantes.innerHTML = data
            document.querySelectorAll('tr img[data-action="eliminar"]').forEach(boton => {
                boton.addEventListener("click", function (e) {
                    if (confirm("¿Seguro que deseas eliminar el participante?")) {
                        var id = this.parentElement.parentElement.getAttribute("data-id")
                        fetch(`eliminarParticipanteDefinitivo.php?participante=${id}`)
                            .then(response => response.text())
                            .then(data => {
                                console.log(data)
                                document.querySelector("#tablaParticipantes").dispatchEvent(new Event("click"))
                            })
                            .catch(error => console.error("hubo un error al eliminar el participante" + error))
                    }
                })
            })

            document.querySelectorAll('img[data-action="modificar"]').forEach(img => {
                img.addEventListener("click", function () {
                    correo = this.parentElement.parentElement.querySelector('input[name="correo"]').value
                    var modal = document.querySelector(".modalContraseña")
                    modal.style.display = "block"
                    modal.querySelector("span").innerHTML = correo
                })
            })
            var campos = document.querySelectorAll("td > input")
            campos.forEach(campo => {
                // console.log("entra")
                campo.addEventListener("blur", function () {
                    var tr = this.closest("tr")
                    let inputs = tr.querySelectorAll("td > input");
                    var id_par = tr.getAttribute("data-id");
                    var formData = new FormData()
                    formData.append("id_participante", id_par)
                    inputs.forEach(input => {
                        formData.append(input.name, input.value)
                    })
                    fetch('modificarParticipante.php', {
                        method: "POST",
                        body: formData
                    })
                        .then(response => response.text())
                        .then(data => alert(data))
                        .catch(error => console.error(error))
                })
            })
        })
        .catch(error => console.log("hubo un error al cargar los participantes: " + error))

    participantes.style.display = "block";
    document.body.style.overflow = "auto"
})

document.getElementById("addCamp").addEventListener("click", function () {
    window.location.reload()
})

document.querySelector("#campeonatos").addEventListener("input", function () {
    torneo = this.value;
    document.querySelector(".tablaParticipantes").style.display = "none"
    document.querySelector(".otraParte").style.display = "block"
    if (this.value != "def") {
        fetch(`cargarTorneo.php?torneo=${torneo}`, {
            headers: {
                "Content-Type": "application/json"
            }
        })
            .then(response => response.json())
            .then(data => {
                const torneoData = data[0];
                document.querySelector('input[name="nombre"]').value = torneoData.nombre;
                document.querySelector('input[name="id_camp"]').value = torneoData.id_campeonato;
                document.querySelector('input[name="localizacion"]').value = torneoData.localizacion;
                document.querySelector('input[name="enlace"]').value = torneoData.enlaceMapa;
                document.querySelector('select[name="participacion"]').value = torneoData.participacion;
                document.querySelector('input[name="libre"]').checked = torneoData.open == 1 ? true : false;
                document.querySelector('input[name="fechaInicio"]').value = torneoData.fechaInicio;
                document.querySelector('input[name="tallaMinima"]').value = torneoData.tallaMinima;
                document.querySelector('select[name="supervisor"]').value = torneoData.supervisor;

                var fecha = document.querySelector('input[name="fechaInicio"]')

                console.log("entra")

                fecha.setAttribute("min",torneoData.fechaInicio)

                let radios = document.querySelectorAll("input[name=\"tipo\"]")
                radios.forEach(radio => {
                    radio.checked = radio.value == torneoData.categoria
                });
                var i = 0
                var jornadas = document.querySelector("ul#jornadas")
                var inner = "";
                while (i < torneoData.numJornadas) {
                    inner += `<li>jornada ${i + 1}</li>`
                    i++;
                }
                jornadas.innerHTML = inner
            })
            .catch(error => console.error("hubo un problema al cargar el torneo: " + error))
        document.getElementById("botonFormu").value = "Modificar"
    }
    else {
        document.querySelector(".otraParte > form").reset();
        document.dispatchEvent(new Event("DOMContentLoaded"))
    }
})

contraValidada = false
correoValido = false
document.getElementById("contraSuper").addEventListener("input", function () {
    let pass = this.value;
    let mensaje = document.getElementById("mensajesContra");
    let errores = [];
    if (pass != "") {
        if (pass.length < 8) errores.push("Debe tener al menos 8 caracteres.");
        if (!/[a-z]/.test(pass)) errores.push("Debe contener una letra minúscula.");
        if (!/[A-Z]/.test(pass)) errores.push("Debe contener una letra mayúscula.");
        if (!/\d/.test(pass)) errores.push("Debe contener un número.");
        if (!/[@$!%*?&.]/.test(pass)) errores.push("Debe contener un carácter especial (@$!%*?&.).");

        if (errores.length === 0) {
            mensaje.innerHTML = "✅ Contraseña segura";
            mensaje.className = "valid";
            contraValidada = true;
        } else {
            mensaje.innerHTML = errores.join("<br>");
            mensaje.className = "error";
            contraValidada = false;
        }

    }
    else {
        mensaje.innerHTML = "";
        contraValidada = true
        correoValido = true
    }
    validarFormulario()

})
function validarFormulario() {
    document.querySelector('.añadirOrganizador > form > input[type="submit"]').disabled = !(contraValidada && correoValido);
}

document.querySelector('.añadirOrganizador > form').addEventListener("submit", function (e) {
    e.preventDefault();
    var correo = this.querySelector('input[type="email"]').value
    var contra = this.querySelector('input[type="password"]').value
    var nombre = this.querySelector('input[type="text"]').value
    fetch('añadirSupervisor.php', {
        method: "POST",
        body: JSON.stringify({
            nombre: nombre,
            correo: correo,
            contraseña: contra
        })
    })
        .then(response => response.text())
        .then(data => {
            console.log(data)
            return fetch('cargarSupervisores.php')
        })
        .then(response => response.text())
        .then(data => {
            var formData = new FormData()

            formData.append("correos[]", correo)
            formData.append("asunto", "Has sido dado de alta como supervisor")
            formData.append("mensaje",'<h1>Has sido dado de alta por el administrador de la aplicación como supervisor</h1><p>Si esto consideras que es un error, ponte en contacto con el administrador &lt;correo@administrador&gt;</p><p style="font-style:italic;">Este correo ha sido generado automaticamente, por favor no respondas</p>')

            fetch("mandarCorreo.php",{
                method:"POST",
                body:formData
            })

            let select = document.getElementById("supervisor");
            select.innerHTML = data;
            if (select.options.length > 0) {
                select.selectedIndex = 0;
            }
        })
        .catch(_ => console.error("hubo un problema al conectar con el servidor"))
    this.reset()
    document.querySelector(".añadirOrganizador").style.display = "none"
})

document.querySelector(".ListaSupervisores > button").addEventListener("click", function () {
    document.querySelector(".añadirOrganizador").style.display = "flex"
})

document.querySelector(".añadirOrganizador > img").addEventListener("click", function () {
    document.querySelector(".añadirOrganizador").style.display = "none"
})

document.getElementById("correo").addEventListener("input", function () {
    var correo = this.value;

    fetch(`comprobarCorreo.php?correo=${correo}`, {
        headers: { "Content-Type": "application/json" }
    })
        .then(response => response.json())
        .then(data => {
            correoValido = !data.existe
            document.querySelector('#add').disabled = data.existe;
            document.querySelector(".mensaje").innerHTML = data.mensaje;
        })
        .catch(error => console.error("Error al comprobar el correo:", error));
})
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("botonFormu").value = "Añadir"
    var actu = new Date().toISOString().split("T")[0];
    document.querySelector('input[type="date"]').setAttribute("min", actu)
    document.querySelector('input[type="radio"]').checked = true

    fetch('cargarSupervisores.php')
        .then(response => response.text())
        .then(data => {
            let select = document.getElementById("supervisor");
            select.innerHTML = data;
            if (select.options.length > 0) {
                select.selectedIndex = 0;
            }
        })
        .catch(_ => console.log("Hubo un error al conectar con el servidor"));


    fetch('sacarCampeonatos.php')
        .then(response => response.text())
        .then(data => {
            document.querySelector("select#campeonatos").innerHTML = data
        })
        .catch(_ => console.log("hubo un error al conectar con el servidor"))
})

document.querySelector(".addJornada").addEventListener("click", function (e) {
    e.preventDefault()
    var lista = document.querySelector("ul#jornadas")
    lista.innerHTML += `<li>jornada ${lista.children.length + 1} </li>`
})

document.querySelector(".otraParte > form").addEventListener("submit", function (e) {
    e.preventDefault()
    var nombre = document.querySelector('input[name="nombre"]')
    var localizacion = document.querySelector('input[name="localizacion"]')
    var enlace = document.querySelector('input[name="enlace"]')
    var categoria = document.querySelector('input[name="tipo"]:checked')
    var participacion = document.querySelector('select[name="participacion"]')
    var libre = document.querySelector('input[name="libre"]');
    var fechaInicio = document.querySelector('input[name="fechaInicio"]')
    var tallaMinima = document.querySelector('input[name="tallaMinima"]')
    var supervisor = document.querySelector('select[name="supervisor"]')
    var id = document.querySelector('input[name="id_camp"]')
    var jornadas = document.querySelector("select#jornadas")



    var formData = new FormData()

    formData.append("nombre", nombre.value)
    formData.append("localizacion", localizacion.value)
    formData.append("enlace", enlace.value)
    formData.append("categoria", categoria.value)
    formData.append("participacion", participacion.value)
    formData.append("fechaInicio", fechaInicio.value)
    formData.append("supervisor", supervisor.value)
    formData.append("libre", libre.checked)
    formData.append("tallaMinima", tallaMinima.value)
    formData.append("numJornadas", jornadas.children.length)
    var boton = document.getElementById("botonFormu")
    if (boton.value == "Añadir") {
        fetch('añadirCampeonato.php', {
            method: "POST",
            body: formData
        })
            .then(response => response.text())
            .then( _ => {
                var formData2 = new FormData()
                formData2.append("correos[]", supervisor.value)
                formData2.append("asunto", "te han asignado un nuevo campeonato")
                formData2.append("mensaje", '<h1>enhorabuena, te han asignado un nuevo campeonato</h1><p>haz click <a href="localhost/ProyectoPesca/">aqui</a> para saber más</p><p style="font-style:italic;">Este correo ha sido generado automaticamente, por favor no respondas</p>')
                fetch("mandarCorreo.php", {
                    method: "POST",
                    body: formData2
                })
                    .then(response => response.text())
                    .then(data => {
                        console.log(data)
                    })
                    .catch(error => console.error("hubo un error al mandar los correos: " + error))
            })
            .catch(_ => console.log("error al conectar con el servidor"))
        this.reset()
        document.dispatchEvent(new Event("DOMContentLoaded"))
    }
    else {
        formData.append("id_camp", id.value)
        fetch('modificarCampeonato.php', {
            method: "POST",
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                alert(data)
                var formData2 = new FormData()
                formData2.append("correos[]", supervisor.value)
                formData2.append("asunto", "te han asignado un nuevo campeonato")
                formData2.append("mensaje", '<h1>enhorabuena, te han asignado un nuevo campeonato</h1><p>haz click <a href="localhost/ProyectoPesca/">aqui</a> para saber más</p><p style="font-style:italic;">Este correo ha sido generado automaticamente, por favor no respondas</p>')
                fetch("mandarCorreo.php", {
                    method: "POST",
                    body: formData2
                })
                    .then(response => response.text())
                    .then(data => console.log(data))
                    .catch(error => console.error("hubo un error al mandar los correos: " + error))
            })
            .catch(error => console.error("Hubo un error al modificar el campeonato " + error))
    }
})
document.querySelector(".delJornada").addEventListener("click", function (e) {
    e.preventDefault()
    let jornadas = document.querySelector("#jornadas")
    jornadas.removeChild(jornadas.lastElementChild)
})
document.querySelector("#cerrarSes").addEventListener("click", function () {
    var a = document.createElement("a")
    a.setAttribute("href", "./")
    a.click();
})
document.querySelector('.modalContraseña input[type="checkbox"]').addEventListener("input", function () {
    this.parentElement.parentElement.querySelectorAll('.contra').forEach(input => {
        input.type = this.checked ? "text" : "password"
    })
})
let contra1Validada = false, contra2Validada = false;
let contra1 = "", contra2 = "";

const mensaje = document.querySelector("#mensajesContra2");
const submit = document.querySelector("#modContraseña");

function validarPassword(pass) {
    let errores = [];
    if (pass.length < 8) errores.push("Debe tener al menos 8 caracteres.");
    if (!/[a-z]/.test(pass)) errores.push("Debe contener una letra minúscula.");
    if (!/[A-Z]/.test(pass)) errores.push("Debe contener una letra mayúscula.");
    if (!/\d/.test(pass)) errores.push("Debe contener un número.");
    if (!/[@$!%*?&.]/.test(pass)) errores.push("Debe contener un carácter especial (@$!%*?&.).");
    return errores;
}

function actualizarEstado() {
    let errores = [];
    if (contra1 == "" && contra2 == "") {
        submit.disabled = false
    }
    else {
        if (contra1Validada && contra2Validada) {
            if (contra1 !== contra2) {
                errores.push("❌ Las contraseñas no coinciden.");
            }
        }

        if (errores.length === 0 && contra1Validada && contra2Validada) {
            mensaje.innerHTML = "✅ Contraseñas válidas y coinciden";
            mensaje.className = "valid";
            submit.disabled = false;
        } else {
            if (errores.length > 0) {
                mensaje.innerHTML = errores.join("<br>");
                mensaje.className = "error";
            }
            submit.disabled = true;
        }
    }
}
document.querySelector(".modalContraseña > img").addEventListener("click", function () {
    this.parentElement.style.display = "none"
})

document.querySelector("#contra1").addEventListener("input", function () {
    contra1 = this.value;
    let errores = [];

    if (contra1 !== "") {
        errores = validarPassword(contra1);
        contra1Validada = errores.length === 0;

        if (!contra1Validada) {
            mensaje.innerHTML = errores.join("<br>");
            mensaje.className = "error";
        }
    } else {
        contra1Validada = false;
        mensaje.innerHTML = "";
    }

    actualizarEstado();
});

document.querySelector("#contra2").addEventListener("input", function () {
    contra2 = this.value;
    let errores = [];

    if (contra2 !== "") {
        errores = validarPassword(contra2);
        contra2Validada = errores.length === 0;

        if (!contra2Validada) {
            mensaje.innerHTML = errores.join("<br>");
            mensaje.className = "error";
        }
    } else {
        contra2Validada = false;
        mensaje.innerHTML = "";
    }

    actualizarEstado();
});
document.querySelector(".modalContraseña > form").addEventListener("submit", function (e) {
    e.preventDefault();
    var correo = this.parentElement.querySelector("span").innerHTML
    var contra = document.querySelector("input#contra1").value


    fetch("modificarContraseña.php", {
        method: "POST",
        body: JSON.stringify({
            correo: correo,
            nuevaContra: contra
        })
    })
        .then(response => response.text())
        .then(data => alert(data))
        .catch(error => console.error("hubo un error al modificar la contraseña: " + error))
    this.reset();
})