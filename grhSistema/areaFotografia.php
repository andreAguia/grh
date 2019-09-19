<?php
/**
 * Área de Licença Prêmio
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
    
    # Roda a rotina que verifica os status
    $reducao = new ReducaoCargaHoraria();
    $reducao->mudaStatus();
	
    # Verifica a fase do programa
    $fase = get('fase');
    
    # Pega o id
    $idPessoa = get('idPessoa');
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou a área de Acumulação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro')))					# Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));	# passa o parametro da session para a variavel parametro retirando as aspas
    else{ 
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro',$parametro);    # transfere para a session para poder recuperá-lo depois
    }
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }
    
    $grid = new Grid();
    $grid->abreColuna(12);
    
################################################################
    
    switch ($fase){
        
        case "" :
        case "lista" :
           
            br();

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $botaoVoltar = new Link("Voltar","grh.php");
            $botaoVoltar->set_class('button');
            $botaoVoltar->set_title('Voltar a página anterior');
            $botaoVoltar->set_accessKey('V');
            $menu1->add_link($botaoVoltar,"left");
            
            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("../grhRelatorios/acumulacao.geral.php");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            #$menu1->add_link($botaoRel,"right");

            $menu1->show();
            
            ###
            
            # Formulário de Pesquisa
            $form = new Form('?'); 

            # Nome    
            $controle = new Input('parametro','texto','Pesquisar:',1);
            $controle->set_size(100);
            $controle->set_title('Pesquisa');
            $controle->set_valor($parametro);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(6);
            $controle->set_autofocus(TRUE);
            $form->add_item($controle);

            $form->show();
            
            ###
                
            # Pega o time inicial
            $time_start = microtime(TRUE);
            
            # Pega os dados
            $select = "SELECT nome,
                              idServidor,
                              idPessoa
                         FROM tbservidor JOIN tbpessoa USING(idPessoa)
                        WHERE situacao = 1
                          AND nome LIKE '%$parametro%'
                     ORDER BY nome";
            
            $resumo = $pessoal->select($select);
            
            if (count($resumo) > 0){
            
                # Monta a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($resumo);
                $tabela->set_label(array("Nome","Lotação","Foto"));
                $tabela->set_align(array("left","left","center"));
                
                $tabela->set_classe(array(NULL,"Pessoal","ExibeFoto"));
                $tabela->set_metodo(array(NULL,"get_Lotacao","show"));

                $tabela->set_titulo("Telefones e Emails da UENF");
                #$tabela->set_idCampo('idServidor');
                #$tabela->set_editar('?fase=editaServidor');            
                $tabela->show();
            }
            
            # Pega o time final
            $time_end = microtime(TRUE);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',')." segundos","right","f10");                        
            break;
        
    ##################################################################
            
            case "exibeFoto" :
                
                # Botão de Voltar
                botaoVoltar("?");
                
                # Nome
                $nome = $pessoal->get_nomeidPessoa($idPessoa);
                
                # Exibe o nome
                titulotable($nome);
            
                $grid = new Grid("center");
                $grid->abreColuna(6);
                
                # Verifica qual arquivo foi gravado
                $arquivo = "../../_fotos/$idPessoa.jpg";
                
                if(file_exists($arquivo)){
                    $arquivo = $arquivo;
                }else{
                    $arquivo = PASTA_FIGURAS.'foto.png';
                }
                
                br();
                
                $painel = new Callout("secondary","center");
                $painel->abre();
                
                $botao = new BotaoGrafico();
                $botao->set_url('?');
                $botao->set_imagem($arquivo,'Foto do Servidor',400,200);
                $botao->set_title('Foto do Servidor');
                $botao->show();
                
                #$foto = new Imagem($arquivo,'Foto do Servidor',300,180);
                #$foto->show();
                
                br(2);
                
                $link = new Link("Alterar Foto","?fase=uploadFoto&idPessoa=$idPessoa");
                $link->set_id("alteraFoto");
                $link->show();
                
                $painel->fecha();
                
                $grid->fechaColuna();
                $grid->fechaGrid();
                break;
                
        ##################################################################
            
            case "uploadFoto" :
                
                # Botão de Voltar
                botaoVoltar("?fase=exibeFoto&idPessoa=$idPessoa");
                
                # Nome
                $nome = $pessoal->get_nomeidPessoa($idPessoa);
                
                # Exibe o nome
                titulotable($nome);
                
                $grid = new Grid("center");
                $grid->abreColuna(6);
                
                # 
                
                echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='foto'>
                        <p>Click aqui ou arraste o arquivo para escolher a foto.</p>
                        <button type='submit' name='submit'>Upload</button>
                    </form>";
                                
                $pasta = "../../_fotos/";
                     
                if ((isset($_POST["submit"])) && (!empty($_FILES['foto']))){
                    $upload = new UploadImage($_FILES['foto'], 1000, 800, $pasta, $idPessoa);
                    echo $upload->salvar();
                    
                    # Registra log
                    $Objetolog = new Intra();
                    $data = date("Y-m-d H:i:s");
                    $atividade = "Alterou a foto do servidor $nome";
                    $Objetolog->registraLog($idUsuario,$data,$atividade,NULL,NULL,4,$idPessoa);
                    
                    # Volta para o menu
                    loadPage("?fase=exibeFoto&idPessoa=$idPessoa");
                }
                
                br(4);                
                callout("Somente é permitido uma foto para cada servidor<br/>E a foto deverá ser no formato jpg ou img.");
                $grid->fechaColuna();
                $grid->fechaGrid();
                break;
                
        ##################################################################
    }
    $grid->fechaColuna();
    $grid->fechaGrid();
            
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}


