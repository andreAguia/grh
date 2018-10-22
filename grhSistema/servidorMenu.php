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
set_session('sessionParametro');	# Zera a session do par�metro de pesquisa da classe modelo1
set_session('sessionPaginacao');	# Zera a session de pagina��o da classe modelo1

# Verifica se veio dos alertas
$alertas = get_session("alertas");

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
    $origem = get('origem',FALSE);
    if($origem){
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro do servidor ".$pessoal->get_nome($idServidorPesquisado);
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7,$idServidorPesquisado);
    }

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
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
    # Limita o tamanho da tela
    $grid = new Grid();
    $grid->abreColuna(12);
    
    if($fase == "menu"){  

        # Cria um menu
        $menu = new MenuBar();

        # Voltar
        if(is_null($alertas)){
            $caminhoVolta = 'servidor.php';
        }else{
            $caminhoVolta = 'grh.php?fase=alertas&alerta='.$alertas;
        }

        $linkBotao1 = new Link("Voltar",$caminhoVolta);
        $linkBotao1->set_class('button');
        $linkBotao1->set_title('Volta para a página anterior');
        $linkBotao1->set_accessKey('V');
        $menu->add_link($linkBotao1,"left");

        # Pasta Funcional
        $linkBotao3 = new Link("Pasta","?fase=pasta");
        $linkBotao3->set_class('button'); 
        $linkBotao3->set_title('Exibe a pasta funcional do servidor');
        $linkBotao3->set_accessKey('P');
        #$menu->add_link($linkBotao3,"right");
        
        # Foto
        $linkBotao3 = new Link("Foto","?fase=uploadFoto");
        $linkBotao3->set_class('button'); 
        $linkBotao3->set_title('Upload uma nova foto');
        $menu->add_link($linkBotao3,"right");

        if(Verifica::acesso($idUsuario,1)){
            # Histórico
            $linkBotao4 = new Link("Histórico","../../areaServidor/sistema/historico.php?idServidor=".$idServidorPesquisado);
            $linkBotao4->set_class('button');
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
        $grid->fechaColuna();
        $grid->fechaGrid();
    }else{
        botaoVoltar("?");
    }
    
    # Exibe os dados do Servidor
    Grh::listaDadosServidor($idServidorPesquisado);
    
    switch ($fase){	
        # Exibe o Menu Inicial
        case "menu" :
            
            # Ocorrencias do servidor
            Grh::exibeOcorênciaServidor($idServidorPesquisado);
            
            # monta o menu do servidor
            Grh::menuServidor($idServidorPesquisado,$idUsuario);
            br();
            
            # Exibe os vinculos anteriores do servidor na uenf (se tiver)
            Grh::exibeVinculos($idServidorPesquisado);
            
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
                $pasta = "../../_arquivo/";

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
                    $menu = new MenuGrafico(1);

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
                        if(substr($arquivo, 0, 5) == "Pasta"){
                            $botao = new BotaoGrafico();
                            $botao->set_label($partesArquivo[0]);
                            $botao->set_url($achei.'/'.$arquivo);
                            $botao->set_target('_blank');
                            $botao->set_image(PASTA_FIGURAS.'pasta.png',$tamanhoImage,$tamanhoImage);
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
                    $menu = new MenuGrafico(4);

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
                            $botao->set_image(PASTA_FIGURAS.'processo.png',$tamanhoImage,$tamanhoImage);
                            $menu->add_item($botao);
                        }
                    }
                    $menu->show();
                }else{               
                    p("Nenhum arquivo encontrado.","center");
                }
                
                 #$callout->fecha();
            $grid->fechaColuna();
            $grid->abreColuna(8);
            }
            
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
            
            case "uploadFoto" :
                $grid = new Grid("center");
                $grid->abreColuna(6);
                
                
                echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                        <input type='file' name='foto'>
                        <p>Click aqui ou arraste o arquivo para escolher a foto.</p>
                        <button type='submit' name='submit'>Upload</button>
                    </form>";
                
                /*
                echo '<form class="upload" action="?fase=uploadFoto" method="POST" enctype="multipart/form-data">
                        <input name="foto" type="file">
                        <p>Drag your files here or click in this area.</p>
                        <button type="submit">Upload</button>
                      </form>';
                */
                                
                $pasta = "../../_arquivo/fotos/";
                     
                if ((isset($_POST["submit"])) && (! empty($_FILES['foto']))){
                    $upload = new UploadImage($_FILES['foto'], 1000, 800, $pasta,$idServidorPesquisado);
                    echo $upload->salvar();
                    loadPage("?");
                }
                
                br();                
                callout("Somente é permitido uma foto para cada servidor<br/>E a foto deverá ser no formato jpg.");
                $grid->fechaColuna();
                $grid->fechaGrid();
                break;
    }

    $grid->fechaColuna();
    $grid->fechaGrid();
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
