
import { messaging, getToken } from './/firebase.js'

var fecha = document.getElementById("fecha");
var hoy = new Date().toISOString().split("T")[0];
fecha.setAttribute("max", hoy);

var contraValidada = false;
var correoValido = false;
var botonRegistro = document.querySelector("#botonRegistro");
botonRegistro.disabled = true;

// Función para validar y habilitar el botón
function validarFormulario() {
    botonRegistro.disabled = !(contraValidada && correoValido);
}

// Validación de la contraseña
document.getElementById("password").addEventListener("input", function () {
    let pass = this.value;
    let mensaje = document.getElementById("mensaje");
    let errores = [];

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

    validarFormulario();
});

setInterval(() => {

},3000)


document.querySelector('input[name="correo"]').addEventListener("input", function () {
    var correo = this.value;

    fetch(`comprobarCorreo.php?correo=${correo}`, {
        headers: { "Content-Type": "application/json" }
    })
        .then(response => response.json())
        .then(data => {
            correoValido = !data.existe;
            document.getElementById("mensajeCorreo").innerHTML = data.mensaje;
            validarFormulario();
        })
        .catch(error => console.error("Error al comprobar el correo:", error));
});

// Validación del teléfono (solo números)
var valorAnterior = "";
document.getElementById("telefono").addEventListener("input", function (e) {
    var valorActual = e.target.value;
    var res = parseInt(valorActual);

    if (isNaN(res) && valorActual !== "") {
        this.value = valorAnterior;
    } else {
        this.value = valorActual;
        valorAnterior = valorActual;
    }
});

// Ocultar secciones al inicio
var dir = document.getElementById("direccion");
dir.style.display = "none";
var otros = document.getElementById("otrosDatos");
otros.style.display = "none";
var dp = document.getElementById("datosPersonales");

// Cambio de secciones en el formulario
document.querySelector("#datosPersonales > form").addEventListener("submit", function (e) {
    e.preventDefault();

    document.getElementById("datosTop").className = "nope";
    document.getElementById("direccionTop").className = "actual";

    dp.style.display = "none";
    dir.style.display = "block";
});

document.querySelector("#direccion > form").addEventListener("submit", function (e) {
    e.preventDefault();

    document.getElementById("direccionTop").className = "nope";
    document.getElementById("otrosTop").className = "actual";

    dir.style.display = "none";
    otros.style.display = "block";
});


document.querySelector("#otrosDatos > form").addEventListener("submit", async function (event) {
    event.preventDefault();

    var formData = new FormData();
    var correo = document.querySelector('input[name="correo"]').value;
    // Datos personales
    formData.append("nombre", document.querySelector('input[name="nombre"]').value);
    formData.append("password", document.querySelector('input[name="password"]').value);
    formData.append("telefono", document.querySelector('input[name="telefono"]').value);
    formData.append("fecha_nacimiento", document.querySelector('input[name="fecha_nacimiento"]').value);
    formData.append("correo", correo);

    // Dirección
    formData.append("direccion", document.querySelector('input[name="direccion"]').value);
    formData.append("cp", document.querySelector('input[name="cp"]').value);
    formData.append("pais", document.querySelector('input[name="pais"]').value);
    formData.append("provincia", document.querySelector('input[name="provincia"]').value);

    // Archivos
    formData.append("imagen", document.querySelector('input[name="imagen"]').files[0]);
    formData.append("licencia", document.querySelector('input[name="licencia"]').value);
    formData.append("federativa", document.querySelector('input[name="federativa"]').value);

    // Permisos (checkbox múltiple)
    let permisosSeleccionados = document.querySelectorAll('input[name="permisos[]"]');
    var algunoSeleccionado = false
    permisosSeleccionados.forEach(permiso => {
        formData.append("permisos[]", permiso.checked ? 1 : 0);
        if (permiso.checked) {
            algunoSeleccionado = true
        }
    });

    var promise = getToken(messaging, {
        vapidKey: "BOg_i-Wk1kRfqJgifQJIfgdbDOtsRkekD-AaBR2Y9-d-17kQNXNGwyODkVKlAKESVoAKpEfu0GOPbvudR_-y-5U"
    })
    var endpoint = "";
    promise.then(function (end) {
        try {
            endpoint = end
            formData.append("endpoint", algunoSeleccionado ? endpoint : null)

            // Enviar datos al servidor
            fetch("guardarRegistro.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    console.log(correo)
                    console.log("Respuesta del servidor:", data);
                    fetch("crearJWT.php",{
                        method:"POST",
                        body:JSON.stringify({
                            correo:correo
                        })
                    })
                    .then(response => response.text())
                    .then(data => {
                        var formCorreo = new FormData();
                        formCorreo.append("correos[]",correo)
                        formCorreo.append("asunto", "nuevo inicio de sesión")
                        formCorreo.append("mensaje", `<p>pulsa <span><a href="localhost/proyectoPesca/verificarCliente.php?token=${data}">aqui</a></span> para verificar tu perfil</p>`)
                        fetch("mandarCorreo.php", {
                            method: "POST",
                            body: formCorreo
                        })
                            .then(response => response.text())
                            .then(data => console.log(data))
                            .catch(error => console.error(error))
                            var a = document.createElement("a")
                            a.setAttribute("href", "pantallaVerificar.php")
                            a.click();
                    })
                })
                .catch(error => console.error("Error en el envío:", error));
        }
        catch (error) {
            console.log("error al resolver el endpoint:" + error)
        }
    })

});
document.getElementById("notis").addEventListener("change", function () {
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
