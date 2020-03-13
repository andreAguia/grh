<?php
/**
 * Pastas e Processos do Servidor
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){
    # Verifica a fase do programa
    $fase = get('fase','listar');
    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # pega o id (se tiver)
    $id = soNumeros(get('id'));

    # Começa uma nova página
    $page = new Page();
    if($fase == "upload"){
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

    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado); 

    # Nome do Modelo
    $objeto->set_nome('Cadastro de documentos na Pasta Funcional');

    # Botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php?fase=pasta');
    
    # select da lista
    $objeto->set_selectLista('SELECT descricao,
                                     idPasta
                                FROM tbpasta
                          WHERE idServidor='.$idServidorPesquisado.'
                       ORDER BY descricao');

    # select do edita
    $objeto->set_selectEdita('SELECT descricao,
                                     idServidor
                                FROM tbpasta
                               WHERE idPasta = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Descrição","Ver"));
    $objeto->set_width(array(80,5));
    $objeto->set_align(array("left"));
        
    #$objeto->set_classe(array(NULL,NULL,NULL,"Pessoal"));
    #$objeto->set_metodo(array(NULL,NULL,NULL,"exibePasta"));

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbpasta');

    # Nome do campo id
    $objeto->set_idCampo('idPasta');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array(
        array ('linha' => 2,
               'nome' => 'descricao',
               'label' => 'Descrição:',
               'tipo' => 'texto',
               'required' => TRUE,
               'autofocus' => TRUE,
               'col' => 12,
               'size' => 250),
        array ('linha' => 3,
               'nome' => 'idServidor',
               'label' => 'Servidor:',
               'tipo' => 'hidden',
               'padrao' => $idServidorPesquisado,
               'col' => 6,
               'size' => 10)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);
    
    # Upload
    if(!vazio($id)){
        $botaoUpload = new Button("Upload","?fase=upload&id=".$id);
        $botaoUpload->set_title("Upload do Documento");

        $objeto->set_botaoEditarExtra(array($botaoUpload));
    }

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
            
            case "upload" :
                $grid = new Grid("center");
                $grid->abreColuna(12);
                                
                # Botão voltar
                botaoVoltar('?fase=editar&id='.$id);
                
                tituloTable("Upload de Documento para Pasta Funcional"); 
                
                $grid->fechaColuna();
                $grid->abreColuna(6);
                
                echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";
                                
                $pasta = "../../_funcional/";
                     
                if ((isset($_POST["submit"])) && (!empty($_FILES['doc']))){
                    $upload = new UploadDoc($_FILES['doc'], $pasta,$id);
                    echo $upload->salvar();
                    
                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $atividade = "Fez o upload de documento para pasta funcional";
                    $Objetolog->registraLog($idUsuario,$data,$atividade,NULL,$id,4,$idServidorPesquisado);
                    
                    # Volta para o menu
                    loadPage("?fase=editar&id=.$id");
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