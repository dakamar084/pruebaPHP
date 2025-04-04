import { messaging,getToken } from "./firebase.js";

var fecha = document.getElementById("fecha");
var hoy = new Date().toISOString().split("T")[0];
fecha.setAttribute("max", hoy)

var valorAnterior = "";
document.getElementById('telefono').addEventListener("input", function(e){
    var valorActual = e.target.value;
    var res = parseInt(valorActual);

    if(isNaN(res)&& valorActual != ""){
        this.value = valorAnterior 
    }
    else{
        this.value = valorActual
        valorAnterior = valorActual 
    }
})

document.getElementById("combo").addEventListener("change", function(e) {
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
document.querySelector("#datosPersonales > form").addEventListener("submit", function (e){
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

    fetch("modificarPersonal.php",{
        method: "POST",
        body:json   
    })
    .then(response => response.text())
    .then(data => alert(data))
    .catch(_=>console.log("hubo un error al conectar con el servidor"))
})
document.querySelector("#direccion > form").addEventListener("submit", function(e){
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
    
    fetch("modificarDireccion.php",{
        method: "POST",
        body:json   
    })
    .then(response => response.text())
    .then(data => alert(data))
    .catch(_=>console.log("hubo un error al conectar con el servidor"))

})
document.querySelector("#otrosDatos").addEventListener("submit", function(e){
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
        if(permiso.checked){
            algunoSeleccionado = true;
        }
    });
    var promise = getToken(messaging,{
        vapidKey:"BOg_i-Wk1kRfqJgifQJIfgdbDOtsRkekD-AaBR2Y9-d-17kQNXNGwyODkVKlAKESVoAKpEfu0GOPbvudR_-y-5U"
    });

    promise.then(function(resp){
        formData.append("endpoint", algunoSeleccionado ? resp:null)
        fetch("modificarOtros.php",{
            method: "POST",
            body:formData
        })
        .then(response=>response.text())
        .then(data => console.log(data))
        .catch(_=>console.error("error al conectar con el servidor"))
    })

})
document.addEventListener("DOMContentLoaded", function() {
    let botones = Array.from(document.querySelectorAll(".botonLateral"));  // Convertir a un array

    botones.forEach(boton => {
        boton.addEventListener("click", function() {
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
            
            switch(aMostrar){
                case "historial":{
                    cargarHistorial()
                    break;
                }
                case "visualizar":{
                    cargarHoy()
                }
            }

            let panelAMostrar = document.getElementById(aMostrar);
            panelAMostrar.style.display = "block";
        });
    });
});



var angulo = false
window.rotar = function(aRotar, camp, talla){
    aRotar.style.transform = angulo ? "rotate(0deg)" : "rotate(90deg)"
    angulo = !angulo
    var datos = aRotar.parentElement.querySelector(".datos")
    fetch(`cargarDatos.php?campeonato=${camp}&talla=${talla}`)
    .then(response =>response.text())
    .then(data => datos.innerHTML = data)
    .catch(_=> console.error("hubo un error al conectar con el servidor"))
    datos.style.display = angulo ? "block" : "none" 
}

window.clickGeneral = function(div){
    div.querySelector("#girar").dispatchEvent(new Event("click"))
}

function cargarHistorial(){
    console.log("entra")
    var contenedor = document.querySelector("#historial")

    fetch("sacarHistorial.php")
    .then(response => response.text())
    .then(data => contenedor.innerHTML = data)
    .catch(_=> console.error("error al conectar con el servidor"))
}

window.apuntarCampeonato = function(id_campeonato, correo){
    var json = `
    {
        "id_campeonato":${id_campeonato},
        "correo":"${correo}"
    }
    `
    fetch("apuntarCampeonato.php",{
        method:"POST",
        body: json
    })
    .then(response => response.text())
    .then(_=> cargarHoy())
    .catch(_ => console.log("hubo un error al conectar con el servidor"))
}

window.cancelarSuscripcion = function(id_campeonato, correo){
    var json = `
    {
        "id_campeonato":${id_campeonato},
        "correo":"${correo}"
    }
    `
    fetch("cancelarSuscripcion.php", {
        method: "POST",
        body:json
    })
    .then(response => response.text())
    .then(_=>cargarHoy())
    .catch(_=> console.error("Hubo un problema al conectar con el servidor"))
}

function cargarHoy(){
    console.log("entra")
    var contenedor = document.querySelector("#visualizar")

    fetch("sacarHoy.php")
    .then(response => response.text())
    .then(data => contenedor.innerHTML = data)
    .catch(_=> console.error("error al conectar con el servidor"))
}
document.getElementById("notis").addEventListener("change", function(){
    if(this.checked){
        Notification.requestPermission(function(permission){
            if(permission == "granted"){
                console.log("el usuario ha permitido las notificaciones")
            }
            else{
                console.log("el usuario ha denegado las notificaciones")
            }
        })
    }
})