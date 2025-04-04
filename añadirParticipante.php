<?php

require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $body = json_decode(file_get_contents("php://input"), true);
    $stm = $cone->prepare("INSERT INTO participantes_turnos (id_participante, id_turno)
                                    SELECT 
                                        (SELECT id_participante FROM participantes WHERE correo LIKE ? LIMIT 1),
                                        t.id_turno
                                    FROM turnos t
                                    WHERE t.id_manga IN (
                                        SELECT id_manga 
                                        FROM mangas 
                                        WHERE id_jornada IN (
                                            SELECT id_jornada 
                                            FROM jornadas 
                                            WHERE id_campeonato = ?
                                        )
                                    )
                                    ON DUPLICATE KEY UPDATE id_participante = id_participante;");
    $stm->bind_param("si",$body["correo"],$body["campeonato"]);
    $stm->execute();

    $destino = $body["correo"];
    $mensaje = '
    <html>
        <head>
        <style>
        h1{
            text-align:center;
        }
        body{
            background-color:aliceblue;
        }
        a{
            text-decoration:none;
            padding:15pt;
            background-color:blue;
            color:white;
        }
        *{
        	margin:30pt;
        }
        </style>
        </head>
        <body>
            <h1>Has sido apuntado en un nuevo campeonato</h1>
            <p>enhorabuena, has sido apundado a un nuevo campeonato de pesca</p>
            <a href="localhost/proyectoPesca/cliente.php">entra aqui para enterarte mas a fondo</a>

            <p style="font-style:Italic;">Este mensaje se ha generado automaticamente, no respondas directamente</p>
        </body>
    </html>
    ';
    $header="From soporte de pesca <dakamar084@gmail.com>\r\n";
    $encabezado = "¡nuevo torneo a la vista!";

    // mail($destino, $encabezado, $mensaje, $header);

    $stm -> get_result();
    echo $stm -> affected_rows >= 1 ? "participante añadido correctamente" : "este participante ya estaba añadido";
}