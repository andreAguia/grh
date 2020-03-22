<?php
/**
 * Cadastro de MCF
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');
    
    # Cria um array com os anos possíveis
    $anoInicial = 1999;
    $anoAtual = date('Y');
    $ano = arrayPreenche($anoAtual,$anoInicial,"d");
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de MCF";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro'))){					# Se o parametro não vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));	# passa o parametro da session para a variavel parametro retirando as aspas
    }else{ 
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro',$parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Começa uma nova página
    $page = new Page();
    if($fase == "uploadMcf"){
        $page->set_ready('$(document).ready(function(){
                                $("form input").change(function(){
                                    $("form p").text(this.files.length + " arquivo(s) selecionado");
                                });
                            });');
    }
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################

    # Nome do Modelo
    $objeto->set_nome('Mapa de Controle de Frequência - MCF');

    # Botão de voltar da lista
    $objeto->set_voltarLista('grh.php');
    
    # controle de pesquisa
    $objeto->set_parametroLabel('Pesquisar');
    $objeto->set_parametroValue($parametro);
    
    # select da lista
    $objeto->set_selectLista ('SELECT idMcf,
                                      ano,
                                      mes,
                                      obs,
                                      idMcf,
                                      idMcf,
                                      idMcf
                                 FROM tbmcf
                                WHERE ano LIKE "%'.$parametro.'%"
                                   OR obs LIKE "%'.$parametro.'%" 
                             ORDER BY ano desc,mes');

    # select do edita
    $objeto->set_selectEdita('SELECT ano,
                                     mes,
                                     obs
                                FROM tbmcf
                               WHERE idMcf = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Id","Ano","Mês","Obs"," Ver","Upload"));
    $objeto->set_width(array(5,10,10,40,10,10));
    $objeto->set_align(array("center","center","center","left"));
    $objeto->set_funcao(array(null,null,"get_nomeMes"));
    
    $objeto->set_classe(array(NULL,NULL,NULL,NULL,"Pessoal"));
    $objeto->set_metodo(array(NULL,NULL,NULL,NULL,"exibeMcf"));
    
    # Botão de Upload
    $botao = new BotaoGrafico();
    $botao->set_label('');    
    $botao->set_url('?fase=uploadMcf&id=');   
    $botao->set_imagem(PASTA_FIGURAS.'upload.png',20,20);

    # Coloca o objeto link na tabela			
    $objeto->set_link(array(NULL,NULL,NULL,NULL,NULL,$botao));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbmcf');

    # Nome do campo id
    $objeto->set_idCampo('idMcf');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 1,
               'nome' => 'ano',
               'label' => 'Ano:',
               'tipo' => 'combo',
               'array' => $ano,
               'required' => TRUE,
               'autofocus' => TRUE,
               'col' => 3,
               'size' => 30),
        array ('linha' => 1,
               'nome' => 'mes',
               'label' => 'Mes:',
               'tipo' => 'combo',
               'array' => $mes,
               'required' => TRUE,
               'col' => 3,
               'size' => 30),
        array ('linha' => 1,
               'nome' => 'obs',
               'label' => 'Obs:',
               'tipo' => 'texto',
               'col' => 6,
               'size' => 80)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

    ################################################################
    switch ($fase){
        case "" :
        case "listar" :
            $objeto->listar();
            break;

        case "editar" :	
        case "excluir" :	
        case "gravar" :
            $objeto->$fase($id);
            break;
        
    ##################################################################
            
            case "uploadMcf" :
                $grid = new Grid("center");
                $grid->abreColuna(12);
                                
                # Botão voltar
                botaoVoltar('?fase=listar');
                
                tituloTable("Upload de MCF"); 
                
                $grid->fechaColuna();
                $grid->abreColuna(6);
                
                echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";
                                
                $pasta = PASTA_MCF;
                
                # Extensões possíveis
                $extensoes = array("pdf");
                
                $texto = "Extensões Permitidas:";
                
                foreach($extensoes as $pp){
                    $texto .= " $pp";
                }
                
                br(2);
                p($texto,"f14","center");
                
                if ((isset($_POST["submit"])) && (!empty($_FILES['doc']))){
                    $upload = new UploadDoc($_FILES['doc'], $pasta, $id, $extensoes);
                    
                    # Salva e verifica se houve erro
                    if($upload->salvar()){
                        
                        # Registra log
                        $Objetolog = new Intra();
                        $data = date("Y-m-d H:i:s");
                        $atividade = "Fez o upload do mcf";
                        $Objetolog->registraLog($idUsuario,$data,$atividade,NULL,$id,4);

                        # Volta para o menu
                        loadPage("?fase=listar");
                    }else{
                        loadPage("?fase=uploadMcf&id=.$id");
                    }
                }
                
                $grid->fechaColuna();
                $grid->fechaGrid();
                break;
                
    ##################################################################
                
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}