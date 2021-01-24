<?php  

    // -------------------- Rutas --------------------
    $nombreProyecto = "os-proyect";
    if(!isset($_GET["carpeta"])){
        $path = $_SERVER['DOCUMENT_ROOT'] . '/'.$nombreProyecto.'/';
        $alternative = 'index.php';
    }else{
        $car = $_GET["carpeta"];
        $path = $_SERVER['DOCUMENT_ROOT'] . '/'.$nombreProyecto.'/'.$car;
        $alternative = 'index.php?carpeta='.$car;
    }

    $cont = 1;
    $contModal = 0;
    // --------- Obtener ficheros de una ruta ---------
    $directorios = [];
    $dir = dir($path);
    while (false !== ($entry = $dir->read())) {
        if ($entry != '.' && $entry != '..') {
            $carpeta = @scandir($path.$entry);
            if($entry != "index.php"){
                $directorios[] = $entry;
            }   
        }
    }

    $informes = [];
    // --------- Obtener petición de realizar alguna acción ---------
    if (isset($_POST['enviar']) && $_POST['varDestino'] !== "") {
            $varDir = $_POST['varDestino'];
            $seleccion = $_POST['seleccion'];
            if($seleccion == 'crearCarpeta'){
                if(count(explode(" ", $varDir))>1){
                    $informes[] = 'El nombre de la capreta no debe contener espacios';
                }else{
                    $result = @mkdir($path.'/'.$varDir);
                    if(!$result){
                        $informes[] = 'La carpeta: '.$varDir.' ya existe';
                        $chg = chmod($path.'/'.$varDir, 0777);
                    }
                }
                
            }
            elseif ($seleccion == 'crearFichero') {
                if(file_exists($path.'/'.$varDir)){
                    $informes[] = 'El archivo: '.$varDir.' ya existe';
                }else{
                    if(count(explode(".", $varDir)) > 1){
                        $result = fopen($path.'/'.$varDir, "a+");
                        $chg = chmod($path.'/'.$varDir, 0777);
                    }else{
                        $informes[] = 'Debe especificar la extensión del archivo';
                    }
                   
                }
                
            }
            elseif ($seleccion == 'renombrar') {
                $seccionado = explode(" ", $varDir);
                $result = rename($path.'/'.$seccionado[0], $path.'/'.$seccionado[1]);
            }
            elseif ($seleccion == 'copiarPegar') {
                $seccionado = explode(" ", $varDir);
                $result = @copy($path.'/'.$seccionado[0], $path.'/'.$seccionado[1]);
                if(!$result){
                    $informes[] = 'No se puede copiar una carpeta';
                }
            }
            elseif ($seleccion == 'cambiarPermisos') {
                $seccionado = explode(" ", $varDir);
                $result = chmod($path.'/'.$seccionado[0], intval($seccionado[1], 8));
                
            }
            elseif ($seleccion == 'moverCortar') {
                $seccionado = explode(" ", $varDir);
                $excep = explode("/", $seccionado[1]);
                if($alternative == 'index.php' && $excep[0] == '..'){
                    $informes[] = 'Accion no permitida';
                }else{
                    $result = rename($path.'/'.$seccionado[0], $path.'/'.$seccionado[1]);
                }
                
            }
            elseif ($seleccion == 'cambiarPropietario'){
                $seccionado = explode(" ", $varDir);
                //$dev = $_GET['device']; $cmd = '/bin/bash /home/www/start.bash '.$dev; echo $cmd; shell_exec($cmd);
                //$result = chown($path.'/'.$seccionado[0], $path.'/'.$seccionado[1]);
                system('sudo chown oukemy Prieba');
            }

            if(empty($informes)){
                header("Location: " . $alternative);
            }
            
    }else if(isset($_POST['varDestino']) && $_POST['varDestino'] == "" ){
        $informes[] = 'No debe dejar el campo vacío';
    }

    // --------------- Editar archivo de texto -----------------------

    if(isset($_POST['modificar'])){
        $nombreDirectorio = $_POST['nombreArchivo'];
        $ac = fopen($path.'/'.$nombreDirectorio, "w");
        fwrite($ac, $_POST['contenido']);
        fclose($ac);
        header("Location: " . $alternative);
    }


    // ----------------- Interpretar persmisos de Linux ----------------- 
    function asignarPermisos($nroPermiso){
        $permiso = "";
        switch($nroPermiso){
            case "0":
                $permiso = "Sin permisos";
                break;
            case "1":
                $permiso = "Sólo ejecución de archivos o acceso a directorios";
                break;
            case "2":
                $permiso = "Sólo escritura";
                break;
            case "3":
                $permiso = "Escritura y ejecución de archivos o acceso a directorios";
                break;
            case "4":
                $permiso = "Sólo lectura";
                break;
            case "5":
                $permiso = "Lectura y ejecución de archivos o acceso a directorios";
                break;
            case "6":
                $permiso = "Lectura y escritura";
                break;
            case "7":
                $permiso = "Lectura, escritura y ejecución de archivos o acceso a directorios";
                break;
                        
        }

        return $permiso;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Bree+Serif&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Sriracha&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/b94857a29d.js" crossorigin="anonymous"></script>
    <title>Explorador de archivos web</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center pt-5">
                <h1 class="" style="opacity:1.0;"><strong>¡Bienvenido a WebHiker!</strong></h1>
                <p class="lead">Explorador web gráfico de archivos simple by: <b class="text-muted">Juan Pablo Morales Rincón</b></p>
                <?php if(isset($_GET["carpeta"])):?>
                    <a class="btn btn-primary btn-sm" href="index.php" role="button">Volver a la raiz</a>
                <?php endif;?>      
            <hr class="my-4">
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="row">                
                    <div class="col-3">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <a class="nav-link" style="color: black;" id="crear-tab" data-toggle="tab" href="#crear" role="tab" aria-controls="crear" aria-selected="true"><i class="fas fa-folder-plus fontf"></i> Crear Carpeta/Archivo</a>
                            <a class="nav-link"  style="color: black;" id="renombrar-tab" data-toggle="tab" href="#renombrar" role="tab" aria-controls="renombrar" aria-selected="false"><i class="fas fa-edit fontf"></i> Renombrar</a>
                            <a class="nav-link" style="color: black;" id="copiar-tab" data-toggle="tab" href="#copiar" role="tab" aria-controls="copiar" aria-selected="false"><i class="fas fa-copy fontf"></i> Copiar/Pegar</a>
                            <a class="nav-link" style="color: black;" id="permisos-tab" data-toggle="tab" href="#permisos" role="tab" aria-controls="permisos" aria-selected="false"><i class="fas fa-edit fontf"></i> Cambiar Permisos</a>
                            <a class="nav-link" style="color: black;" id="moverCortar-tab" data-toggle="tab" href="#moverCortar" role="tab" aria-controls="moverCortar" aria-selected="false"><i class="fas fa-edit fontf"></i> Mover/Cortar</a>
                        </div>
                    </div>
                    <div class="col-9">
                        <div class="tab-content" id="v-pills-tabContent">
                            <div class="tab-pane fade text-justify mt-2 show active" id="crear" role="tabpanel" aria-labelledby="crear-tab">Para crear una capeta o una archivo, solo basta con seleccinar la opción Crear carpeta/Crear fichero. Ten encuenta que al crear una carpeta, esta no debe contener espacios, también, si vas a crear un archivo, asegúrate de que contenga la extensión correspondiente (ejem: SoyUnArchivo.txt)</div>
                            <div class="tab-pane fade text-justify mt-2" id="renombrar" role="tabpanel" aria-labelledby="renombrar-tab">Para renombrar un fichero (ya sea una carpeta o un archivo), tendrás que escribir el nombre del fichero a modificar y luego de un espacio, el nombre nuevo. Para los archivos, en el nuevo nombre también tendrás que especificar la extensión. Para las carpetas, el nuevo nombre no debe contener espacios</div>
                            <div class="tab-pane fade text-justify mt-2" id="copiar" role="tabpanel" aria-labelledby="copiar-tab">Para copiar el contenido de un archivo de texto a otro, solo basta con escribir el nombre del archivo origen seguido de un espacio, luego la dirección del archivo de destino. Si el archivo de texto destino no existe, se creará uno con el nombre de este, con el contenido del arhivo del origen. (Para el nombre tanto del archivo de origen como de destino, especificar la extensión)</div>
                            <div class="tab-pane fade text-justify mt-2" id="permisos" role="tabpanel" aria-labelledby="permisos-tab">Para cambiar los permisos de un fichero, bastará con escribir el nombre de este, seguido de un espacio, y luego los nuevos permisos en octal. (Ej: img 600). Ver documentacón de permisos de linux en octal siendo necesario.</div>
                            <div class="tab-pane fade text-justify mt-2" id="moverCortar" role="tabpanel" aria-labelledby="moverCortar-tab">Para mover/cortar un fichero a otra carpeta, basta con escribir el nombre del archivo (con su extensión), seguido de un espacio, y luego la ruta de destino con un slash (/) que indicará el nuevo nombre. Ejemplo: hola.txt Prueba/hola.txt Esto moverá el archivo hola.txt a la carpeta Prueba con el mismo nombre, pero esto último es opcional, puedes colocar el nombre que quieras.</div>
                        </div>
                    </div>
                </div>
                <hr class="my-4">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12"> 
                <div class="row">
                    <div class="col-md-12">
                        <form method="POST" class="form-inline mb-3">
                            <label class="mr-sm-2 sr-only" for="inlineFormCustomSelect">Preference</label>
                            <select class="custom-select mr-sm-2" id="inlineFormCustomSelect" name="seleccion">
                                <option name="crearCarpeta" value="crearCarpeta" selected>Crea carpeta</option>
                                <option name="crearFichero" value="crearFichero">Crear fichero</option>
                                <option name="renombrar" value="renombrar">Renombrar</option>
                                <option name="copiarPegar" value="copiarPegar">Copiar/Pegar</option>
                                <option name="cambiarPermisos" value="cambiarPermisos">Cambiar permisos</option>
                                <option name="moverCortar" value="moverCortar">Mover/Cortar</option>
                                <option name="cambiarPropietario" value="cambiarPropietario">Cambiar propietario</option>
                            </select>
                    
                            <label for="Destino" class="sr-only">Destino</label>
                            <input name="varDestino" type="text" class="form-control rounded-0" id="Destino" placeholder="Información">
                            <button type="submit" class="btn btn-secondary rounded-0" name="enviar">Enviar</button>
                        </form> 
                    </div>
                    <div class="col-md-12">
                        <?php if(isset($informes)):?>
                            <?php foreach($informes as $i):?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $i;?>    
                                </div>
                            <?php endforeach;?>
                        <?php endif;?>
                    </div>
                </div>   

                <div class="row">
                    <?php foreach($directorios as $directorio):?>
                        
                        <?php 
                            // -------- Rutas ------------
                            $arr = null;
                            $ruta = "";
                            $arr = explode("/", dirname($path."/".$directorio));
                            $clave = array_search($nombreProyecto, $arr) + 1;
                            for ($i=$clave; $i < count($arr); $i++) { 
                                $ruta .= $arr[$i] . "/";
                            } 

                            // -------- Permisos ----------

                            $strPermisos = substr(sprintf('%o', fileperms($path.'/'.$directorio)), -4);
                            $arrPermisos = null;
                            $arrPermisos = str_split($strPermisos);
                            
                            $permisosPropietario = asignarPermisos($arrPermisos[1]);
                            $permisosGrupo = asignarPermisos($arrPermisos[2]);
                            $permisosOtros = asignarPermisos($arrPermisos[3]);

                            // -------- Propietario ----------- (Solo funciona para Linux)

                            /* $id =  substr(sprintf('%o', fileowner($path.'/'.$directorio)), -4) . "<br />";
                                $info =  posix_getpwuid(fileowner($directorio)); 
                                $propietarioNombre =  $info['name'];   Solo funciona en linux  */ 
                                
                            // -------- Leer Archivo de texto ---
                            $archivo = null; 
                            $esTxt = false;
                            if(!is_dir($path.'/'.$directorio)){
                                $extension = explode(".", $directorio);
                                if(count($extension) > 1 && $extension[1] == 'txt'){
                                    $archivo = file_get_contents($path.'/'.$directorio);
                                    $archivo = ucfirst($archivo);
                                    $archivo = nl2br($archivo);
                                    $contModal ++;
                                    $esTxt = true;
                                }
                            }

                        ?>

                        <div class="col-md-3">

                            <div class="card ba" style="width: 18rem;">
                                <img src="img/<?php echo (is_dir($path.'/'.$directorio)) ? 'descarga.png': 'archivo.png'?>" class="card-img-top" alt="...">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $directorio?></h5>
                                    <p class="card-text">Propietario: Juan Pablo Morales <!--<?php // echo $propietarioNombre;?> --></p>
                                </div>
                                <ul class="list-group list-group-flush">
                                    <div class="dropdown mr-1 list-group-item">
                                        <button type="button" class="btn btn-light btn-sm btn-block dropdown-toggle" id="dropdownMenuOffset" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-offset="10,20">
                                            Permisos
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuOffset">
                                            <a class="dropdown-item" href="">Propietario: <?php echo $permisosPropietario?></a>
                                            <a class="dropdown-item" href="">Grupo: <?php echo $permisosGrupo?></a>
                                            <a class="dropdown-item" href="">Otros: <?php echo $permisosOtros?></a>
                                            <a class="dropdown-item" href="">Linux: <?php echo $strPermisos?></a>
                                        </div>
                                    </div>
                                </ul>
                                <div class="card-body">
                                    <?php if(is_dir($path.'/'.$directorio) && !$esTxt):?>
                                        <a href="<?php echo 'index.php?carpeta='.$ruta.$directorio?>" class="btn btn-success btn-block">Navegar</a>
                                    <?php elseif ($esTxt):?>
                                        <button type="button" class="btn btn-dark" data-toggle="modal" data-target="#archivoModal<?php echo $contModal;?>">Ver contenido</button>
                                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editarContenido<?php echo $contModal;?>">Editar</button>

                                        <div class="modal fade" id="archivoModal<?php echo $contModal;?>" tabindex="-1" role="dialog" aria-labelledby="archivoModalLabel<?php echo $contModal;?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="archivoModalLabel<?php echo $contModal;?>"><?php echo $directorio?></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <?php echo $archivo;?>
                                            </div>
                                            </div>
                                        </div>
                                        </div>
                                        
                                        <div class="modal fade" id="editarContenido<?php echo $contModal;?>" tabindex="-1" role="dialog" aria-labelledby="editarArchivoModalLabel<?php echo $contModal;?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editarArchivoModalLabel<?php echo $contModal;?>"><?php echo $directorio?></h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST">
                                                            <div class="form-group">
                                                                <input type="hidden" name="nombreArchivo" value="<?php echo $directorio?>">
                                                                <label for="message-text" class="col-form-label">Editar/Agregar contenido:</label>
                                                                <textarea class="form-control" id="message-text" name="contenido" style="height:300px"><?php echo $archivo;?></textarea>
                                                            </div>
                                                            <div class="form-row">
                                                                <div class="col">
                                                                    <button type="submit" class="btn btn-success btn-block" name="modificar">Confirmar</button>
                                                                </div>
                                                                <div class="col">
                                                                    <button type="submit" class="btn btn-danger btn-block" data-dismiss="modal">Cancelar</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else:?>
                                        <button class="btn btn-danger btn-block not-allowed" disabled>
                                            Ver contenido
                                        </button>
                                    <?php endif;?>
                                </div>
                            </div>

                        </div>
                    <?php endforeach;?>
                    
                </div>

            
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js" integrity="sha384-+YQ4JLhjyBLPDQt//I+STsc9iw4uQqACwlvpslubQzn4u2UU2UFM80nGisd026JF" crossorigin="anonymous"></script>

</body>
    <style>
        body{
            background-image: url("img/bg7.png");
            font-family: 'Bree Serif', serif;
            
        }
        .not-allowed{
            cursor: not-allowed;
        }

        .fontf{
            font-size: 20px;
        }


        .ba:hover{
            opacity: 1.0;
        }

        a.active{ 
            background-color: black !important;
            color: white !important;
        }

        .nav-item:hover{
            opacity: 0.8;
        }
    </style>
</html>