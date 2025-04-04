

document.querySelector("#tablaParticipantes").addEventListener("click", function(){
    console.log("entra")
    var contenido = document.querySelector(".otraParte")
    contenido.style.display = "none"

    var participantes = document.querySelector(".tablaParticipantes")

    fetch('cargarParticipantes.php')
    .then(response => response.text())
    .then(data=>{
        participantes.innerHTML = data
        var campos = document.querySelectorAll("td > input")
        campos.forEach(campo=>{
            campo.addEventListener("blur",function(){
                var tr = this.closest("tr")
                let inputs = tr.querySelectorAll("td > input");
                var id_par = tr.getAttribute("data-id");
                var formData = new FormData()
                formData.append("id_participante", id_par)
                inputs.forEach(input=>{
                    formData.append(input.name, input.value)
                })
                fetch('modificarParticipante.php',{
                    method:"POST",
                    body:formData
                })
                .then(response => response.text())
                .then(data => console.log(data))
                .catch(error=>console.error(error))
            })
        })
    })
    .catch(error=>console.log("hubo un error al cargar los participantes: "+error))
    
    participantes.style.display = "block";
    document.body.style.overflow = "auto"
})

document.getElementById("addCamp").addEventListener("click", function(){
    window.location.reload()
})

document.querySelector("#campeonatos").addEventListener("input", function () {
    torneo = this.value;
    document.querySelector(".tablaParticipantes").style.display = "none"
    document.querySelector(".otraParte").style.display = "block"
    if(this.value != "def"){
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

                let radios = document.querySelectorAll("input[name=\"tipo\"]")
                radios.forEach(radio => {
                    radio.checked = radio.value == torneoData.categoria
                });
                var i = 0
                var jornadas = document.querySelector("select#jornadas")
                var inner = "";
                while(i < torneoData.numJornadas){
                    inner += `<option value="jornada${i+1}">jornada ${i+1}</option>`
                    i++;
                } 
                jornadas.innerHTML = inner
            })
            .catch(error => console.error("hubo un problema al cargar el torneo: " + error))
            document.getElementById("botonFormu").value = "Modificar"
        }
        else{
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
        .then(data => document.querySelector("#campeonatos").innerHTML = data)
        .catch(_ => console.log("hubo un error al conectar con el servidor"))
    })
    
    document.querySelector(".addJornada").addEventListener("click", function (e) {
        e.preventDefault()
    var lista = document.querySelector("select#jornadas")
    lista.innerHTML += `<option value="jornada ${lista.children.length + 1}">jornada ${lista.children.length + 1} </option>`
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
    if(boton.value == "Añadir"){
        fetch('añadirCampeonato.php', {
            method: "POST",
            body: formData
        })
            .then(response => response.text())
            .then(data => console.log(data))
            .catch(_ => console.log("error al conectar con el servidor"))
            this.reset()
            document.dispatchEvent(new Event("DOMContentLoaded"))
    }
    else{
        formData.append("id_camp",id.value)
        fetch('modificarCampeonato.php',{
            method:"POST",
            body:formData
        })
        .then(response=>response.text())
        .then(data => alert(data))
        .catch(error=> console.error("Hubo un error al modificar el campeonato "+error))
    }
    })
document.querySelector(".delJornada").addEventListener("click", function (e) {
    e.preventDefault()
    let jornadas = document.querySelector("#jornadas")
    jornadas.remove(jornadas.options.length - 1)
})