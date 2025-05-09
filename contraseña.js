const correoInput = document.getElementById('correo');
document.addEventListener('DOMContentLoaded', () => {
    correoInput.dispatchEvent(new Event('input', { bubbles: true }));
});
if (correoInput) {
    correoInput.addEventListener('input', (e) => {
        var correo = e.target.value;
        fetch(`comprobarCorreo.php?correo=${correo}`)
            .then(response => response.json())
            .then(data => {
                var enviar = document.querySelector("input[type='submit']");
                enviar.disabled = !data.existe;
            })
            .catch(error => console.error('Error:', error));
    });
}
const form = document.getElementById('formulario');
if (form) {
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        var correo = correoInput.value;
        fetch("generarCodigo.php")
            .then(response => response.text())
            .then(data => {
                var formData = new FormData();
                formData.append("correos[]", correo);
                formData.append("asunto", "Recuperar contraseña");
                formData.append("mensaje", `<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Document</title></head><body><div style='text-align: center; font-family: Arial, sans-serif; margin-top: 50px;'><h1 style='color: #333;'>Código para cambiar contraseña</h1><div style='background-color: #f0f0f0; padding: 20px; border-radius: 5px; display: inline-block;'><p style='font-size: 18px; color: #555;'>Tu código es: <strong style='color: #007BFF;'>${data}</strong></p></div></div></body></html>`);
                fetch("mandarCorreo.php", {
                    method: "POST",
                    body: formData
                })
                    .then(response => response.text())
                    .then(data => {
                        console.log(data);
                        document.querySelector("div.main").style.display = "none";
                        document.querySelector("div.correoEnviado").style.display = "flex";
                    })
                    .catch(error => console.error('Error:', error));
            })


    });
}
var form2 = document.getElementById('formulario2');
form2.addEventListener('submit', (e) => {
    e.preventDefault();
    var codigos = document.querySelectorAll(".codigo");
    var codigo = Array.from(codigos).map(c => c.value).join("");
    fetch("comprobarCodigo.php", {
        method: "POST",
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `codigo=${codigo}&correo=${correo}`
    })
        .then(response => response.json())
        .then(data => {
            if(data.exito){
                document.querySelector("div.correoEnviado").style.display = "none";
                document.querySelector("div.cambiarContraseña").style.display = "flex";
                document.querySelector("div.codigoIncorrecto").style.display = "none";
            }
            else{
                document.querySelector("div.codigoIncorrecto").style.display = "flex";
            }
        })
        .catch(error => console.error('Error:', error));
});
document.querySelector("#nuevaContra > #contra1").addEventListener("input", function (e) {
    var botonSubmit = document.querySelector("#nuevaContra > input[type='submit']");
    var errores = [];
    var pass2 = document.querySelector("#nuevaContra > #contra2").value;
    var pass = e.target.value;
    if (pass.length < 8) errores.push("Debe tener al menos 8 caracteres.");
    if (!/[a-z]/.test(pass)) errores.push("Debe contener una letra minúscula.");
    if (!/[A-Z]/.test(pass)) errores.push("Debe contener una letra mayúscula.");
    if (!/\d/.test(pass)) errores.push("Debe contener un número.");
    if (!/[@$!%*?&.]/.test(pass)) errores.push("Debe contener un carácter especial (@$!%*?&.).");
    if (pass !== pass2) errores.push("Las contraseñas no coinciden.");
    var errorDiv = document.querySelector("#nuevaContra > .errores");
    errorDiv.innerHTML = errores.join("<br>");
    errorDiv.style.display = errores.length > 0 ? "block" : "none";
    botonSubmit.disabled = errores.length > 0;
    botonSubmit.setAttribute("title", errores.length > 0 ? "Las contraseñas no coinciden o no cumplen con los requisitos minimos" : "Enviar");    
});
document.querySelector("#nuevaContra > #contra2").addEventListener("input", function (e) {
    var botonSubmit = document.querySelector("#nuevaContra > input[type='submit']");
    var errores = [];
    var pass2 = document.querySelector("#nuevaContra > #contra1").value;
    var pass = e.target.value;
    if (pass !== pass2) errores.push("Las contraseñas no coinciden.");
    var errorDiv = document.querySelector("#nuevaContra > .errores");
    errorDiv.innerHTML = errores.join("<br>");
    errorDiv.style.display = errores.length > 0 ? "block" : "none";
    botonSubmit.disabled = errores.length > 0;
    botonSubmit.setAttribute("title", errores.length > 0 ? "Las contraseñas no coinciden" : "Enviar");    
});
document.querySelector('#nuevaContra input[type="checkbox"]').addEventListener("change", function (e) {
    var contras = document.querySelectorAll("#nuevaContra input[type='password']");
    contras.forEach(function (contrasena) {
        contrasena.type = e.target.checked ? "text" : "password";
    });
})
var codigos = document.querySelectorAll(".codigo");
codigos.forEach(function (codigo) {

    codigo.addEventListener("input", function () {
        if(codigo.value.length >= 1) {
            var siguiente = parseInt(codigo.id.split("codigo")[1])+1;
            var siguienteInput = document.getElementById("codigo"+siguiente);
            if(siguienteInput){
                siguienteInput.focus();
            }
            else{
                console.log("aqui")
                document.querySelector("#formulario2").dispatchEvent(new Event("submit"))
            }
        }
    })
    codigo.addEventListener("paste", function (e) {
        const paste = (e.clipboardData || window.clipboardData).getData('text').trim();
        var dividido = paste.split("");
        var inputs = document.querySelectorAll(".codigo");
        inputs.forEach(function (input, index) {
            if (dividido[index]) {
                input.value = dividido[index];
            }
        });
        document.querySelector("#formulario2").dispatchEvent(new Event("submit"))

    })
    codigo.addEventListener("keydown", function (e) {
        if (e.key === "Backspace" && codigo.value.length === 0) {
            var anterior = parseInt(codigo.id.split("codigo")[1])-1;
            var anteriorInput = document.getElementById("codigo"+anterior);
            if(anteriorInput){
                anteriorInput.focus();
            }
            else{

            }
        }
    })
})

document.querySelector("#nuevaContra").addEventListener("submit", function (e) {
    e.preventDefault();
    var correo = correoInput.value;
    var pass= document.querySelector("#nuevaContra > #contra1").value;
    fetch("modificarContraseña.php",{
        method: "POST",
        body: JSON.stringify({correo: correo, nuevaContra: pass})
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        var a = document.createElement("")
        a.setAttribute("href", "./")
        a.click()
    })
})