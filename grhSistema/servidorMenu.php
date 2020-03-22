<?php

/**
 * Menu de Servidores
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Zera session usadas
set_session('sessionParametro');	# Zera a session do parâmetro de pesquisa da classe modelo1
set_session('sessionPaginacao');	# Zera a session de paginação da classe modelo1

# Verifica a origem 
$origem = get_session("origem");
$origemId = get_session("origemId");

# Verifica se veio dos alertas
$alerta = get_session("alerta");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','menu');
    
    # Pega os parâmetros
    $parametroAno = post('parametroAno',get_session('parametroAno',date("Y")));
        
    # Joga os parâmetros par as sessions    
    set_session('parametroAno',$parametroAno);
    
    # Registra no log  
    $atividade = "Visualizou o cadastro do servidor ".$pessoal->get_nome($idServidorPesquisado);
    $data = date("Y-m-d H:i:s");
    $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7,$idServidorPesquisado);
    
    # Começa uma nova página
    $page = new Page();
    if($fase == "uploadFoto"){
        $page->set_ready('$(document).ready(function(){
                                $("form input").change(function(){
                                    $("form p").text(this.files.length + " arquivo(s) selecionado");
                                });
                            });');
    }
    $page->iniciaPagina();
    
    if(($fase <> "despacho") AND
       ($fase <> "despachoChefia")){
        
        # Cabeçalho da Página
        AreaServidor::cabecalho();
    }
    
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);
    
    if($fase == "menu"){  

        # Cria um menu
        $menu = new MenuBar();
        
        # Verifica se veio de um alerta
        if(!is_null($alerta)){
            $caminhoVolta = 'grh.php?fase=alerta&alerta='.$alerta;
        }else{
            if(is_null($origem)){
                $caminhoVolta = 'servidor.php';
            }else{
                $caminhoVolta = $origem;
            }
        }
        
        $linkBotao1 = new Link("Voltar",$caminhoVolta);
        $linkBotao1->set_class('button');
        $linkBotao1->set_title('Volta para a página anterior');
        $linkBotao1->set_accessKey('V');
        $menu->add_link($linkBotao1,"left");

        if(Verifica::acesso($idUsuario,1)){
            
            # Histórico
            $linkBotao4 = new Link("Histórico","../../areaServidor/sistema/historico.php?idServidor=".$idServidorPesquisado);
            $linkBotao4->set_class('button success');
            $linkBotao4->set_title('Exibe as alterações feita no cadastro desse servidor');        
            $linkBotao4->set_accessKey('H');
            $menu->add_link($linkBotao4,"right");
            
            # Excluir
            $linkBotao5 = new Link("Excluir","servidorExclusao.php");
            $linkBotao5->set_class('alert button');
            $linkBotao5->set_title('Excluir Servidor');
            $linkBotao5->set_accessKey('x');
            $menu->add_link($linkBotao5,"right");
        }

        $menu->show();
        
    }elseif($fase == "pasta"){  
        
        # Cria um menu
        $menu = new MenuBar();
        
        $linkBotao1 = new Link("Voltar","?");
        $linkBotao1->set_class('button');
        $linkBotao1->set_title('Volta para a página anterior');
        $linkBotao1->set_accessKey('V');
        $menu->add_link($linkBotao1,"left");
        
        $menu->show();
        
    }else{
        if(($fase <> "despacho") AND
           ($fase <> "despachoChefia")){
            botaoVoltar("?");
        }
    }
    
    if(($fase <> "despacho") AND
       ($fase <> "despachoChefia")){
        
        # Exibe os dados do Servidor
        Grh::listaDadosServidor($idServidorPesquisado);
    }
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    switch ($fase){	
        # Exibe o Menu Inicial
        case "menu" :
            
            # Ocorrencias do servidor
            #Grh::exibeOcorênciaServidor($idServidorPesquisado);
            
            # Exibe os vinculos anteriores do servidor na uenf (se tiver)
            #Grh::exibeVinculos($idServidorPesquisado);
            
            # monta o menu do servidor
            #Grh::menuServidor($idServidorPesquisado,$idUsuario);
            $menu = new MenuServidor($idServidorPesquisado,$idUsuario);
            
            # Exibe o rodapé da página
            Grh::rodape($idUsuario,$idServidorPesquisado,$pessoal->get_idPessoa($idServidorPesquisado));
            break;
        
        ##################################################################	
        
        case "pasta" :
            # Pasta Funcional
            
            # Pega o idfuncional do servidor Pesquisado
            $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
            
             # So continua se tiver id cadastrado
            if(is_null($idFuncional)){
                br(3);
                p("Servidor sem idFuncional cadastrada no sistema","center");
                p("E necessario ter a idfuncional cadastrada para poder vizualizar a pasta.","center");
            }else{
            
                $grid = new Grid();
                $grid->abreColuna(4);

                # Título
                tituloTable('Pasta Funcional');

                br();
            
                # Define a pasta
                $pasta = "../../_arquivos/_arquivo/";

                $achei = NULL;

                # Encontra a pasta
                foreach (glob($pasta.$idFuncional."*") as $escolhido) {
                    $achei = $escolhido;
                }

                # Verifica se tem pasta desse servidor
                if(file_exists($achei)){

                    $grupoarquivo = NULL;
                    $contador = 0;

                    # Inicia o menu
                    $tamanhoImage = 60;
                    $menu = new MenuGrafico();

                    # pasta
                    $ponteiro  = opendir($achei."/");
                    while ($arquivo = readdir($ponteiro)) {

                        # Desconsidera os diretorios 
                        if($arquivo == ".." || $arquivo == "."){
                            continue;
                        }

                        # Verifica a codificação do nome do arquivo
                        if(codificacao($arquivo) == 'ISO-8859-1'){
                            $arquivo = utf8_encode($arquivo);
                        }

                        # Divide o nome do arquivos
                        $partesArquivo = explode('.',$arquivo);

                        # Verifica se arquivo é da pasta
                        if(substr($arquivo, 0, 5) == "Pasta"){
                            $botao = new BotaoGrafico();
                            $botao->set_label($partesArquivo[0]);
                            $botao->set_url($achei.'/'.$arquivo);
                            $botao->set_target('_blank');
                            $botao->set_imagem(PASTA_FIGURAS.'pasta.png',$tamanhoImage,$tamanhoImage);
                            $menu->add_item($botao);

                            $contador++;
                        }
                    }
                    if($contador >0){
                        $menu->show();
                    }
                }else{                
                    p("Nenhum arquivo encontrado.","center");
                }

                #$callout->fecha();
                $grid->fechaColuna();
                $grid->abreColuna(8);

                #############################################################

                tituloTable('Processos');
                br();

                # Verifica se tem pasta desse servidor
                if(file_exists($achei)){

                    $grupoarquivo = NULL;

                    # Inicia o menu
                    $tamanhoImage = 60;
                    $menu = new MenuGrafico();

                    # pasta
                    $ponteiro  = opendir($achei."/");
                    while ($arquivo = readdir($ponteiro)) {

                        # Desconsidera os diretorios 
                        if($arquivo == ".." || $arquivo == "."){
                            continue;
                        }

                        # Verifica a codificação do nome do arquivo
                        if(codificacao($arquivo) == 'ISO-8859-1'){
                            $arquivo = utf8_encode($arquivo);
                        }

                        # Divide o nome do arquivos
                        $partesArquivo = explode('.',$arquivo);


                        # VErifica se arquivo é da pasta
                        if(substr($arquivo, 0, 5) <> "Pasta"){
                            $botao = new BotaoGrafico();
                            $botao->set_label($partesArquivo[0]);
                            $botao->set_url($achei.'/'.$arquivo);
                            $botao->set_target('_blank');
                            $botao->set_imagem(PASTA_FIGURAS.'processo.png',$tamanhoImage,$tamanhoImage);
                            $menu->add_item($botao);
                        }
                    }
                    $menu->show();
                }else{               
                    p("Nenhum arquivo encontrado.","center");
                }
                
                $grid->fechaColuna();
                $grid->fechaGrid();    
               
            }
            
            break;
            
        ##################################################################	
        
        case "pasta2" :
            # Pasta Funcional
            
                $grid = new Grid();
                $grid->abreColuna(4);

                # Título
                tituloTable('Pasta Funcional');

                br();
            
                # Define a pasta
                $pasta = PASTA_FUNCIONAL;
                
                # Pega os documentos
                $select = "SELECT idPasta, descricao
                             FROM tbpasta
                            WHERE idServidor = $idServidorPesquisado
                              AND tipo = 1";
                
                $dados = $pessoal->select($select);
                $count = $pessoal->count($select);
                
                if($count > 0){
                    
                    # Inicia o menu
                    $menu = new MenuGrafico(1);
                    
                    foreach($dados as $dd){
                        
                        # Monta o arquivo
                        $arquivo = $pasta.$dd[0].".pdf";
                        
                        # Procura o arquivo
                        if(file_exists($arquivo)){
                            
                            # Monta o botão
                            $botao = new BotaoGrafico();
                            $botao->set_label($dd[1]);
                            $botao->set_url($arquivo);
                            $botao->set_target('_blank');
                            $botao->set_imagem(PASTA_FIGURAS.'pasta.png',$tamanhoImage,$tamanhoImage);
                            $menu->add_item($botao);
                        }
                        
                    }
                    
                    $menu->show();
                    
                }else{
                    p("Nenhum arquivo encontrado.","center");
                }
                
                
                $grid->fechaColuna();
                $grid->abreColuna(8);

                #############################################################

                tituloTable('Processos');
                br();
                
                $grid->fechaColuna();
                $grid->fechaGrid();
            
            break;

        ##################################################################
            
            case "timeline" :
                
            # Nome
            
            $grid = new Grid();
            $grid->abreColuna(12);
            
            #tituloTable("Afastamentos Anuais");
            
            # Formulário de Pesquisa
            $form = new Form('?fase=timeline');

            # Cria um array com os anos possíveis
            $anoInicial = 1999;
            $anoAtual = date('Y');
            $anos = arrayPreenche($anoInicial,$anoAtual+2);

            $controle = new Input('parametroAno','combo','Ano:',1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Ano');
            $controle->set_array($anos);
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            $form->show();
            
            # Define a data de hoje
            $hoje = date("d/m/Y");

            # Gráfico
            $select1 = "(SELECT CONCAT('Férias',' - ',anoExercicio) as descricao,
                              dtInicial,
                              numDias,
                              ADDDATE(dtInicial,numDias-1) as dtFinal
                         FROM tbferias
                        WHERE idServidor = $idServidorPesquisado
                          AND YEAR(dtInicial) = $parametroAno  
                     ORDER BY dtInicial) UNION 
                       (SELECT CONCAT(tbtipolicenca.nome,' ',IFNULL(tbtipolicenca.lei,'')) as descricao,
                              dtInicial,
                              numDias,
                              ADDDATE(dtInicial,numDias-1) as dtFinal
                         FROM tblicenca LEFT JOIN tbtipolicenca USING(idTpLicenca) 
                        WHERE idServidor = $idServidorPesquisado
                          AND YEAR(dtInicial) = $parametroAno  
                     ORDER BY dtInicial) UNION 
                       (SELECT 'Licença Prêmio' as descricao,
                              dtInicial,
                              numDias,
                              ADDDATE(dtInicial,numDias-1) as dtFinal
                         FROM tblicencapremio
                        WHERE idServidor = $idServidorPesquisado
                          AND YEAR(dtInicial) = $parametroAno  
                     ORDER BY dtInicial) UNION 
                       (SELECT 'Trabalho TRE' as descricao,
                              data,
                              dias,
                              ADDDATE(data,dias-1) as dtFinal
                         FROM tbtrabalhotre
                        WHERE idServidor = $idServidorPesquisado
                          AND YEAR(data) = $parametroAno  
                     ORDER BY data) UNION 
                       (SELECT 'Folga TRE' as descricao,
                              data,
                              dias,
                              ADDDATE(data,dias-1) as dtFinal
                         FROM tbfolga
                        WHERE idServidor = $idServidorPesquisado
                          AND YEAR(data) = $parametroAno  
                     ORDER BY data) UNION 
                       (SELECT 'Outros' as descricao,
                              '$parametroAno-01-01' as dtInicial,
                              NULL,
                              '$parametroAno-01-01' as dtFinal
                         FROM tblicencapremio) UNION 
                       (SELECT 'Outros' as descricao,
                              '$parametroAno-12-31' as dtInicial,
                              NULL,
                              '$parametroAno-12-31' as dtFinal
                         FROM tblicencapremio) order by 2";

            # Acessa o banco
            $pessoal = new Pessoal();
            $atividades1 = $pessoal->select($select1);
            $numAtividades = $pessoal->count($select1);
            $contador = $numAtividades; // Contador pra saber quando tirar a virgula no último valor do for each linhas abaixo.

            tituloTable("Afastamentos de $parametroAno");

            if($numAtividades > 0){

                # Carrega a rotina do Google
                echo '<script type="text/javascript" src="'.PASTA_FUNCOES_GERAIS.'/loader.js"></script>';

                # Inicia o script
                echo "<script type='text/javascript'>";
                echo "google.charts.load('current', {'packages':['timeline'], 'language': 'pt-br'});
                      google.charts.setOnLoadCallback(drawChart);
                      function drawChart() {
                            var container = document.getElementById('timeline');
                            var chart = new google.visualization.Timeline(container);
                            var dataTable = new google.visualization.DataTable();";

                echo "dataTable.addColumn({ type: 'string' });
                      dataTable.addColumn({ type: 'date' });
                      dataTable.addColumn({ type: 'date' });";

                echo "dataTable.addRows([";

                $separador = '-';
                
                foreach ($atividades1 as $row){

                    # Trata a data inicial
                    $dt1 = explode($separador,$row['dtInicial']);
                    $dt2 = explode($separador,$row['dtFinal']);
                    
                    echo "['".$row['descricao']."', new Date($dt1[0], $dt1[1]-1, $dt1[2]), new Date($dt2[0], $dt2[1]-1, $dt2[2])]";
                    
                    $contador--;
                    
                    if($contador > 0){
                        echo ",";
                    }
                }
                echo "]);";
                
                echo "var options = { 
                             timeline: { colorByRowLabel: true},
                             backgroundColor: '#f2f2f2',
                             };";
                echo "chart.draw(dataTable, options);";
                echo "}";
                echo "</script>";

                #[ 'Washington', new Date(1789, 3, 30), new Date(1797, 2, 4) ],
                #[ 'Adams',      new Date(1797, 2, 4),  new Date(1801, 2, 4) ],
                #[ 'Jefferson',  new Date(1801, 2, 4),  new Date(1809, 2, 4) ]]);
                
                $altura = ($numAtividades * 45) + 50;
                echo '<div id="timeline" style="height: '.$altura.'px; width: 100%;"></div>';
                
            }else{
                br();
                p("Não há dados para serem exibidos.","f14","center");
            }
            
            #$grid->fechaColuna();
            #$grid->abreColuna(4);
            
            # Tabela
            $select2 = "(SELECT CONCAT('Férias',' - ',anoExercicio) as descricao,
                              dtInicial,
                              numDias,
                              ADDDATE(dtInicial,numDias-1) as dtFinal
                         FROM tbferias
                        WHERE idServidor = $idServidorPesquisado
                          AND YEAR(dtInicial) = $parametroAno  
                     ORDER BY dtInicial) UNION 
                       (SELECT CONCAT(tbtipolicenca.nome,'<br/>',IFNULL(tbtipolicenca.lei,'')) as descricao,
                              dtInicial,
                              numDias,
                              ADDDATE(dtInicial,numDias-1) as dtFinal
                         FROM tblicenca LEFT JOIN tbtipolicenca USING(idTpLicenca) 
                        WHERE idServidor = $idServidorPesquisado
                          AND YEAR(dtInicial) = $parametroAno  
                     ORDER BY dtInicial) UNION 
                       (SELECT 'Licença Prêmio' as descricao,
                              dtInicial,
                              numDias,
                              ADDDATE(dtInicial,numDias-1) as dtFinal
                         FROM tblicencapremio
                        WHERE idServidor = $idServidorPesquisado
                          AND YEAR(dtInicial) = $parametroAno  
                     ORDER BY dtInicial) order by 2";
            
            # Acessa o banco
            $atividades2 = $pessoal->select($select2);
            
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($atividades2);
            $tabela->set_label(array("Afastamento","Inicial","Dias","Final"));
            $tabela->set_align(array("left","center"));
            #$tabela->set_totalRegistro(FALSE);
            $tabela->set_funcao(array(NULL,"date_to_php",NULL,"date_to_php"));
            $tabela->set_titulo("Tabela");
            
            #$numAtividades = $pessoal->count($select2);
            #if($numAtividades > 0){
            #    $tabela->show();
            #}else{
            #    tituloTable("Tabela");
            #    br();
            #    p("Não há dados para serem exibidos.","f14","center");
            #}
            
            $grid->fechaColuna();
            $grid->fechaGrid();    
            break;
        
        ##################################################################
            
            case "exibeFoto" :
                $grid = new Grid("center");
                $grid->abreColuna(6);
                
                $fotoLargura = 300;
                $fotoAltura = 400;

                # Define a pasta
                $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
                
                # Verifica qual arquivo foi gravado
                $arquivo = PASTA_FOTOS."$idPessoa.jpg";
                
                $painel = new Callout("secondary","center");
                $painel->abre();
                
                # Monta o Menu
                $menu = new MenuGrafico(1);
                
                # Verifica se tem pasta desse servidor
                if(file_exists($arquivo)){
                    $botao = new BotaoGrafico("foto");
                    $botao->set_url('?');
                    $botao->set_imagem($arquivo,$fotoLargura,$fotoAltura);
                    $botao->set_title('Foto do Servidor');
                    $menu->add_item($botao);
                }else{
                    $botao = new BotaoGrafico("foto");
                    $botao->set_url('?');
                    $botao->set_imagem(PASTA_FIGURAS.'foto.png',$fotoLargura,$fotoAltura);
                    $botao->set_title('Servidor sem foto cadastrada');
                    $menu->add_item($botao);
                }

                $menu->show();
                
                br(2);
                
                $link = new Link("Alterar Foto","?fase=uploadFoto");
                $link->set_id("alteraFoto");
                $link->show();
                
                $painel->fecha();
                
                $grid->fechaColuna();
                $grid->fechaGrid();
                break;
                
        ##################################################################
            
            case "uploadFoto" :
                
                $grid = new Grid("center");
                $grid->abreColuna(6);
                
                # Gera a área de upload
                echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='foto'>
                        <p>Click aqui ou arraste o arquivo.</p>
                        <button type='submit' name='submit'>Enviar</button>
                    </form>";
                                
                $pasta = PASTA_FOTOS;
                
                # Extensões possíveis
                $extensoes = array("jpg");
                
                # Pega os valores do php.ini
                $postMax = limpa_numero(ini_get('post_max_size'));
                $uploadMax = limpa_numero(ini_get('upload_max_filesize'));
                $limite = menorValor(array($postMax,$uploadMax));
                
                $texto = "Extensões Permitidas:";
                                
                foreach($extensoes as $pp){
                    $texto .= " $pp";
                }
                
                #$texto .= "<br/>Tamanho Máximo do Arquivo: $limite M";
                
                br();
                p($texto,"f14","center");
                
                $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
                     
                if ((isset($_POST["submit"])) && (!empty($_FILES['foto']))){
                    $upload = new UploadImage($_FILES['foto'], 1000, 800, $pasta,$idPessoa);
                    # Salva e verifica se houve erro
                    if($upload->salvar()){
                        
                        # Registra log
                        $Objetolog = new Intra();
                        $data = date("Y-m-d H:i:s");
                        $atividade = "Alterou a foto do servidor ".$pessoal->get_nome($idServidorPesquisado);
                        $Objetolog->registraLog($idUsuario,$data,$atividade,NULL,NULL,4,$idServidorPesquisado);

                        # Volta para o menu
                        loadPage("?");
                    }else{
                        loadPage("?fase=uploadFoto");
                    }
                }
                
                $grid->fechaColuna();
                $grid->fechaGrid();
                break;
                
        ##################################################################
            
            case "despacho" :
                # Limita a tela
                $grid = new Grid("center");
                $grid->abreColuna(10);
                br(3);

                # Título
                titulo("Despacho Para Abertura de Processo no Protocolo");
                $painel = new Callout();
                $painel->abre();

                # Monta o formulário
                $form = new Form('../grhRelatorios/despacho.Protocolo.php');

                # folha da publicação no processo 
                $controle = new Input('assunto','texto','Assunto:',1);
                $controle->set_size(200);
                $controle->set_linha(1);
                $controle->set_col(12);
                $controle->set_autofocus(TRUE);
                $controle->set_title('O assunto do processo.');
                $form->add_item($controle);

                # submit
                $controle = new Input('imprimir','submit');
                $controle->set_valor('Imprimir');
                $controle->set_linha(5);
                $controle->set_col(2);
                $form->add_item($controle);

                $form->show();
                
                $grid->fechaColuna();
                $grid->fechaGrid();
                break;     
        ##################################################################
            
            case "despachoChefia" :
                # Limita a tela
                $grid = new Grid("center");
                $grid->abreColuna(10);
                br(3);
                
                # Pega os valores
                $idServidorChefia = $pessoal->get_chefiaImediata($idServidorPesquisado);
                $nomeChefia = $pessoal->get_nome($idServidorChefia);
                $chefiaImediataDescricao = $pessoal->get_chefiaImediataDescricao($idServidorPesquisado);

                # Título
                titulo("Despacho para Chefia - Resultado Setor - Ato Servidor");
                $painel = new Callout();
                $painel->abre();

                # Monta o formulário
                $form = new Form('../grhRelatorios/despacho.AtoReitor.php');

                # Chefia
                $controle = new Input('chefia','texto','Chefia:',1);
                $controle->set_size(200);
                $controle->set_linha(1);
                $controle->set_col(12);
                $controle->set_valor($nomeChefia);
                $controle->set_autofocus(TRUE);
                $controle->set_title('A chefia imediata.');
                $form->add_item($controle);
                
                # Cargo da Chefia
                $controle = new Input('cargo','texto','Cargo da Chefia:',1);
                $controle->set_size(200);
                $controle->set_linha(1);
                $controle->set_col(12);
                $controle->set_valor($chefiaImediataDescricao);
                $controle->set_title('O cargo da chefia imediata.');
                $form->add_item($controle);
                
                # Cargo da Chefia
                $controle = new Input('ato','texto','Ato de:',1);
                $controle->set_size(200);
                $controle->set_linha(1);
                $controle->set_col(12);
                $controle->set_title('O Ato ao qual o despacho se refere.');
                $form->add_item($controle);

                # submit
                $controle = new Input('imprimir','submit');
                $controle->set_valor('Imprimir');
                $controle->set_linha(5);
                $controle->set_col(2);
                $form->add_item($controle);

                $form->show();
                
                $grid->fechaColuna();
                $grid->fechaGrid();
                break;
            
            ##################################################################
            
            case "afastamentoGeral" :
                
                $grid = new Grid("center");
                $grid->abreColuna(12);
                
                # Formulário de Pesquisa
                $form = new Form('?fase=afastamentoGeral');

                # Cria um array com os anos possíveis
                $anoInicial = 1999;
                $anoAtual = date('Y');
                $anos = arrayPreenche($anoInicial,$anoAtual+2);

                $controle = new Input('parametroAno','combo','Ano:',1);
                $controle->set_size(8);
                $controle->set_title('Filtra por Ano');
                $controle->set_array($anos);
                $controle->set_valor($parametroAno);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(3);
                $form->add_item($controle);

                $form->show();
            
                $afast = new Afastamento();
                $afast->set_idServidor($idServidorPesquisado);
                $afast->set_ano($parametroAno);
                $afast->exibeTabela();
                #$afast->exibeTimeline();
                
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
