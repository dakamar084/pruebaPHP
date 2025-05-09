<?php
session_start();

$correo = $_SESSION["correo"];

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "GET"){
    $stm = $cone -> prepare("SELECT pe.id_equipo,pe.id_participante, pe.rol, e.nombre, e.tipoEquipo from jugadores_equipo pe inner join participantes p on pe.id_participante = p.id_participante inner join equipo e on e.id_equipo = pe.id_equipo where p.correo like ? and pe.estado like 'aceptado'");
    $stm -> bind_param("s", $correo);
    $stm -> execute();
    $res = $stm -> get_result();

    if($res -> num_rows >= 1){
        $equipo = $res -> fetch_assoc();
        $stm_participantes = $cone -> prepare("SELECT p.id_participante, p.nombre, p.correo, je.rol from participantes p inner join jugadores_equipo je on je.id_participante = p.id_participante where je.id_equipo = ? and je.estado like 'aceptado'");
        $stm_participantes -> bind_param("i", $equipo["id_equipo"]);
        $stm_participantes -> execute();

        $participantes = $stm_participantes -> get_result();

        echo '<div class="lista"><h1> Lista de participantes de <span class="nombreEquipo" data-tipo="'.$equipo["tipoEquipo"].'">'.$equipo["nombre"].'</span>:</h1><button class="abandonarEquipo">Abandonar Equipo</button></div>';
        echo '<div class="participantes">';
        while($row = $participantes -> fetch_assoc()){
            $img_eliminar = $equipo["rol"] == "admin" && $row["rol"] != "admin" ? '<img src="cerrar.png" alt="eliminar participante"/>' : '';
            $nombre = $equipo["id_participante"] == $row["id_participante"] ? $row["nombre"].' (tú)' : $row["nombre"];
            echo'
                <div class="participante">
                    <p title="'.$row["correo"].'">'.$nombre.'</p>
                    '.$img_eliminar.'
                </div>
            ';
        }
        echo '
            </div>';

        $stm_invitar = $cone -> prepare("SELECT nombre, correo from participantes where id_participante not in(SELECT id_participante from jugadores_equipo where id_equipo = ?)");
        $stm_invitar -> bind_param("i", $equipo["id_equipo"]);

        $stm_invitar -> execute();

        $aInvitar = $stm_invitar -> get_result();
        if($equipo["rol"] == "admin"){
            echo '<div class="invitar"><select>
                    <option value="def">invitar a un participante</option>';
            while($row = $aInvitar -> fetch_assoc()){
                echo '
                    <option value="'.$row["correo"].'">'.$row["correo"].'</option>
                ';
            }
            echo '</select><button class="invitarParticipante" title="selecciona un participante para invitarle al equipo" disabled>Invitar participante</button></div>';
        }

    }
    else{
        $stm_ofertas = $cone -> prepare("SELECT nombre from equipo where id_equipo in(SELECT id_equipo from jugadores_equipo where id_participante in(SELECT id_participante from participantes where correo like ?) and estado like 'pendiente')");
        $stm_ofertas -> bind_param("s", $correo);
        $stm_ofertas -> execute();
        $res = $stm_ofertas -> get_result();
        $cantidad = $res -> num_rows != 0 ? '<p class="cantidad">'.$res -> num_rows.'</p>' : "";
        echo '
        <div class="solicitudes">
            '.$cantidad.'
            <img src="solicitud.png"/>
            <div class="ofertas" style="display:none;">';
            
            if($res -> num_rows == 0){
                echo "<p> no tienes ofertas actualmente </p>";
            }
            else{
                while($row = $res -> fetch_assoc()){
                    echo '
                    <div class="oferta">
                        <p>'.$row["nombre"].'</p>
                        <button class="aceptar">Aceptar</button>
                        <button class="rechazar">Rechazar</button>
                    </div>
                    ';
                }
            }
            echo '
            </div>
        </div>
        <p>todavia no formas parte de ningun equipo, <span class="enlace">¿Quieres crear alguno?</span></p>';
    }
}