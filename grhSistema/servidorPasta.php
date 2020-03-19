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
    $fase = get('fase','exibe');
    
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
    $objeto->set_nome('Documentos da Pasta Funcional');

    # Botão de voltar da lista
    $objeto->set_voltarLista('?fase=exibe&id='.$id);
    $objeto->set_voltarForm('?fase=listar');
        
    # select da lista
    $objeto->set_selectLista('SELECT CASE tipo
                                        WHEN 1 THEN "Pasta"
                                        WHEN 2 THEN "Processo"
                                        WHEN 3 THEN "Documento"
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
    $objeto->set_width(array(15,65,5));
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
                                               array(1,'Pasta'),
                                               array(2,'Processo'),
                                               array(3,'Documento')),
                              'size' => 20,
                              'title' => 'Qual o tipo de Docuemnto',
                              'col' => 4,
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
                              'col' => 6,
                              'size' => 10)));

    # idUsuário para o Log
    $objeto->set_idUsuario($idUsuario);

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

            if(Verifica::acesso($idUsuario,1)){
                
                # Editar
                $linkBotao5 = new Link("Editar","?fase=listar");
                $linkBotao5->set_class('button');
                $linkBotao5->set_title('Editar Documentos da Pasta do Servidor');
                $menu->add_link($linkBotao5,"right");
            }

            $menu->show();
            
            # Dados do Servidor
            get_DadosServidor($idServidorPesquisado);
            
            # Documentos            
            $painel = new Callout();
            $painel->abre();

            # Título
            tituloTable('Documentos da Pasta Funcional');

            br();

            # Define a pasta
            $pasta = "../../_funcional/";

            # Pega os documentos
            $select = "SELECT idPasta, 
                              descricao,
                              tipo
                         FROM tbpasta
                        WHERE idServidor = $idServidorPesquisado";

            $dados = $pessoal->select($select);
            $count = $pessoal->count($select);

            if($count > 0){

                # Inicia o menu
                $menu = new MenuGrafico();

                foreach($dados as $dd){

                    # Monta o arquivo
                    $arquivo = $pasta.$dd[0].".pdf";
                    
                    # Procura o arquivo
                    if(file_exists($arquivo)){
                        
                        # Define as variáveis
                        $figura = 'documentacao.png';
                        
                        # Define o tipo para saber qual o icone
                        switch ($dd[2]){
                            case 1 :
                                $figura = 'pasta.png';
                                break;
                            
                            case 2 :
                                $figura = 'processo.png';
                                break;
                            
                            case 3 :
                                $figura = 'documentacao.png';
                                break;
                        }

                        # Monta o botão
                        $botao = new BotaoGrafico();
                        $botao->set_label($dd[1]);
                        $botao->set_url($arquivo);
                        $botao->set_target('_blank');
                        $botao->set_imagem(PASTA_FIGURAS.$figura,50,50);
                        $menu->add_item($botao);
                        
                    }
                }

                $menu->show();

            }else{
                p("Nenhum arquivo encontrado.","center");
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
                
                tituloTable("Upload de Documento para Pasta Funcional"); 
                
                $grid->fechaColuna();
                $grid->abreColuna(6);
                
                echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='doc'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";
                                
                $pasta = "../../_funcional/";
                
                # Extensões possíveis
                $extensoes = array("pdf");
                
                $texto = "Extensões Permitidas:";
                
                foreach($extensoes as $pp){
                    $texto .= " $pp";
                }
                
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
                        $Objetolog->registraLog($idUsuario,$data,$atividade,NULL,$id,4,$idServidorPesquisado);

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