<?php
/**
 * Pastas e Pasta Funcional
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
    $fase = get('fase','exibe');
    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Pasta Funcional";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7,$idServidorPesquisado);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Verifica a origem 
    $origem = get_session("origem");

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
    $objeto->set_nome('Documentos da Pasta Funcional');
    
    # Botão de voltar da lista
    if(vazio($origem)){
        $caminhoVolta = '?fase=exibe&id='.$id;
    }else{
        $caminhoVolta = $origem;
    }
    
    $objeto->set_voltarLista($caminhoVolta);
    $objeto->set_voltarForm('?fase=listar');
        
    # select da lista
    $objeto->set_selectLista('SELECT CASE tipo
                                        WHEN 1 THEN "Documento"
                                        WHEN 2 THEN "Processo"
                                     END,
                                     descricao,
                                     idPasta,
                                     idPasta
                                FROM tbpasta
                          WHERE idServidor='.$idServidorPesquisado.'
                       ORDER BY descricao');

    # select do edita
    $objeto->set_selectEdita('SELECT tipo,
                                     descricao,
                                     idServidor
                                FROM tbpasta
                               WHERE idPasta = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Tipo","Descrição","Ver","Upload"));
    $objeto->set_width(array(15,65,5,5));
    $objeto->set_align(array("center","left"));
        
    $objeto->set_classe(array(NULL,NULL,"PastaFuncional"));
    $objeto->set_metodo(array(NULL,NULL,"exibePasta"));
    
    # Botão de Upload
    $botao = new BotaoGrafico();
    $botao->set_label('');    
    $botao->set_url('?fase=upload&id=');   
    $botao->set_imagem(PASTA_FIGURAS.'upload.png',20,20);

    # Coloca o objeto link na tabela			
    $objeto->set_link(array("","","",$botao));

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
                        array('nome' => 'tipo',
                              'label' => 'Tipo:',
                              'tipo' => 'combo',
                              'autofocus' => TRUE,
                              'required' => TRUE,
                              'array' => array(array(NULL,NULL),
                                               array(1,'Documento'),
                                               array(2,'Processo')),
                              'size' => 20,
                              'title' => 'Qual o tipo de Docuemnto',
                              'col' => 3,
                              'linha' => 1),        
                       array ('linha' => 1,
                              'nome' => 'descricao',
                              'label' => 'Descrição:',
                              'tipo' => 'texto',
                              'required' => TRUE,        
                              'col' => 8,
                              'size' => 250),
                       array ('linha' => 3,
                              'nome' => 'idServidor',
                              'label' => 'Servidor:',
                              'tipo' => 'hidden',
                              'padrao' => $idServidorPesquisado,
                              'col' => 9,
                              'size' => 10)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################
    switch ($fase){
        case "" :
        case "exibe" :
            $grid = new Grid();
            $grid->abreColuna(12);
            
            # Cria um menu
            $menu = new MenuBar();

            $linkBotao1 = new Link("Voltar","servidorMenu.php");
            $linkBotao1->set_class('button');
            $linkBotao1->set_title('Volta para a página anterior');
            $linkBotao1->set_accessKey('V');
            $menu->add_link($linkBotao1,"left");

            if(Verifica::acesso($idUsuario,4)){
                
                # Editar
                $linkBotao5 = new Link("Editar","?fase=listar");
                $linkBotao5->set_class('button');
                $linkBotao5->set_title('Editar Documentos da Pasta do Servidor');
                $menu->add_link($linkBotao5,"right");
            }

            $menu->show();
            
            # Dados do Servidor
            get_DadosServidor($idServidorPesquisado);
            
            $grid->fechaColuna();
            
            ###############################################################
            
            # Documentos
            $grid->abreColuna(6);
            
            # Painel
            $painel = new Callout();
            $painel->abre();
            
            # Pega os documentos
            $select = "SELECT idPasta, 
                              descricao,
                              tipo
                         FROM tbpasta
                        WHERE tipo = 1 AND idServidor = $idServidorPesquisado";

            $dados = $pessoal->select($select);
            $count = $pessoal->count($select);

            if($count > 0){
                
                # Cabeçalho da tabela
                $titulo = 'Documentos';
                $label = array(NULL,NULL);
                $width = array(15,80);
                $align = array('center','left');            

                # Exibe a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($dados);
                $tabela->set_align($align);
                $tabela->set_label($label);
                $tabela->set_width($width);
                $tabela->set_titulo($titulo);
                $tabela->set_funcao(array("exibeDocumentoPasta"));
                $tabela->set_totalRegistro(FALSE);
                $tabela->show();
            }else{
                tituloTable($titulo);
                br(2);
                
                p("Nenhum arquivo encontrado.","f14","center");
            }
            
            $painel->fecha();            
            $grid->fechaColuna();
            
            ###################################################33
            # Processos            
            $grid->abreColuna(6);
            
            # Painel
            $painel = new Callout();
            $painel->abre();

            # Pega os documentos
            $select = "SELECT idPasta, 
                              descricao,
                              tipo
                         FROM tbpasta
                        WHERE tipo = 2 AND idServidor = $idServidorPesquisado";

            $dados = $pessoal->select($select);
            $count = $pessoal->count($select);

            if($count > 0){
                
                # Cabeçalho da tabela
                $titulo = 'Processos';
                $label = array(NULL,NULL);
                $width = array(15,80);
                $align = array('center','left');            

                # Exibe a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($dados);
                $tabela->set_align($align);
                $tabela->set_label($label);
                $tabela->set_width($width);
                $tabela->set_titulo($titulo);
                $tabela->set_funcao(array("exibeProcessoPasta"));
                $tabela->set_totalRegistro(FALSE);
                $tabela->show();
            }else{
                tituloTable($titulo);
                br(2);
                
                p("Nenhum arquivo encontrado.","f14","center");
            }
            
            $painel->fecha();
                
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
            
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
                botaoVoltar('?fase=listar');
                
                # Dados do Servidor
                get_DadosServidor($idServidorPesquisado);
                
                tituloTable("Upload de Documento para Pasta Funcional"); 
                
                $grid->fechaColuna();
                $grid->abreColuna(6);
                
                echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";
                                
                $pasta = PASTA_FUNCIONAL;
                
                # Extensões possíveis
                $extensoes = array("pdf");
                
                # Pega os valores do php.ini
                $postMax = limpa_numero(ini_get('post_max_size'));
                $uploadMax = limpa_numero(ini_get('upload_max_filesize'));
                $limite = menorValor(array($postMax,$uploadMax));
                
                $texto = "Extensões Permitidas:";
                                
                foreach($extensoes as $pp){
                    $texto .= " $pp";
                }
                
                $texto .= "<br/>Tamanho Máximo do Arquivo: $limite M";
                br();
                p($texto,"f14","center");
                     
                if ((isset($_POST["submit"])) && (!empty($_FILES['doc']))){
                    $upload = new UploadDoc($_FILES['doc'], $pasta, $id, $extensoes);
                    
                    # Salva e verifica se houve erro
                    if($upload->salvar()){
                        # Registra log
                        $Objetolog = new Intra();
                        $data = date("Y-m-d H:i:s");
                        $atividade = "Fez o upload de documento para pasta funcional";
                        $Objetolog->registraLog($idUsuario,$data,$atividade,"tbpasta",$id,8,$idServidorPesquisado);

                        # Volta para o menu
                        loadPage("?fase=listar");
                    }else{
                        loadPage("?fase=upload&id=.$id");
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