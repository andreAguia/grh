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
	
    # Verifica a fase do programa
    $fase = get('fase',"resumo");
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou a área de Formação";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega os parâmetros    
    $parametroLotacao = post('parametroLotacao',get_session('parametroLotacao','Todos'));
    $parametroSituacao = post('parametroSituacao',get_session('parametroSituacao','Todos'));
    
    # Joga os parâmetros par as sessions   
    set_session('parametroLotacao',$parametroLotacao);
    set_session('parametroSituacao',$parametroSituacao);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
    $grid = new Grid();
    $grid->abreColuna(12);
    
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
    $botaoRel->set_url("../grhRelatorios/sispatriLotacao.php");
    $botaoRel->set_target("_blank");
    $botaoRel->set_imagem($imagem);
    #$menu1->add_link($botaoRel,"right");

    $menu1->show();

    # Titulo
    titulo("Área do Sispatri");
    br();
            
################################################################
    
    switch ($fase){
        case "resumo" :

            
            
        ##############
            
            # Pega os dados
            $select ='SELECT tbservidor.idfuncional,
                             tbpessoa.nome,
                             tbservidor.idServidor,
                             tbservidor.idServidor,
                             tbservidor.idServidor
                        FROM tbsispatri LEFT JOIN tbservidor USING (idServidor)
                                             JOIN tbpessoa USING (idPessoa)
                                             JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                             JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                       WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    
            # Lotacao
            if($parametroLotacao <> "Todos"){
                # Verifica se o que veio é numérico
                if(is_numeric($parametroLotacao)){
                    $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")'; 
                }else{ # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")';
                }
            }
            
            # Situação
            if($parametroSituacao <> "Todos"){
                $select .= ' AND tbservidor.situacao = '.$parametroSituacao;
            }

            $select .= ' ORDER BY 2';
            
            #echo $select;
                        
        #########
            
            $numSispatri = $pessoal->count($select);
            
            $grid = new Grid("center");

            ## Coluna do menu            
            $grid->abreColuna(12,3);
                
                # Menu
                $painel = new Callout();
                $painel->abre();
                
                    titulo("Menu");
                    br();

                    $itens = array(
                        array('Resumo','resumo'),
                        array('CI','ci'),
                        array('Importar','importar'));

                $menu = new Menu();
                $menu->add_item('link','Resumo','?fase=resumo');
                
                if($numSispatri > 0){
                    $menu->add_item('linkWindow','CI','../grhRelatorios/ciSispatri.php');
                }
                $menu->add_item('link','Importar','?fase=importar');
                $menu->show();

                $painel->fecha();

                # Número de Servidores
                if($parametroSituacao == 1){
                    $painel = new Callout();
                    $painel->abre();

                    titulo("Resumo");
                    br();

                    $texto1 = Null;
                    $texto2 = Null;


                        if($parametroLotacao == "Todos"){
                            $numServidores = $pessoal->get_numServidoresAtivos();
                            $texto1 = "$numServidores Servidores Ativos";
                        }else{
                            $numServidores = $pessoal->get_numServidoresAtivos($parametroLotacao);
                            $texto1 = "$numServidores Servidor(es) Ativo(s) <br/> Nesta Lotação";
                        }

                        switch ($numSispatri){

                            case 0 :
                                $texto2 = "Todos Fizeram o Sispatri !!<br/>Fantástico !!!";
                                break;

                            case 1 :
                                $texto2 = "Somente 1 Servidor <br/> Não Fez o Sispatri";
                                break;

                            case ($numSispatri == $numServidores) :
                                $texto2 = "Ninguém Fez o Sispatri !!!<br/> Que Loucura !!";
                                break;

                            case ($numSispatri>1) :
                                $texto2 = "$numSispatri Servidores <br/> Não Fizeram o Sispatri";
                                break;
                        }

                        # Chart
                        $chart = new Chart("Pie",array(array("Fez Sispatri",$numServidores-$numSispatri),array("Não Fez Sispatri",$numSispatri)));
                        $chart->set_idDiv("sispatri");
                        $chart->set_legend(FALSE);
                        $chart->set_tamanho($largura = "50%",$altura = "50%");
                        $chart->show();

                        p($texto1,"estatisticaTexto");
                        p($texto2,"estatisticaTexto");

                    $painel->fecha();
                }
            
            $grid->fechaColuna();

        ##############

            # Coluna de Conteúdo
            $grid->abreColuna(12,9);  
            
            # Formulário de Pesquisa
            $form = new Form('?');
            
            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                          FROM tblotacao
                                         WHERE ativo) UNION (SELECT distinct DIR, DIR
                                          FROM tblotacao
                                         WHERE ativo)
                                      ORDER BY 2');
            array_unshift($result,array('Todos','-- Todos --'));

            $controle = new Input('parametroLotacao','combo','Lotação:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(8);
            $form->add_item($controle);
            
             # Situação
            $result = $pessoal->select('SELECT idsituacao, situacao
                                          FROM tbsituacao                                
                                      ORDER BY 1');
            array_unshift($result,array('Todos','-- Todos --'));

            $controle = new Input('parametroSituacao','combo','Situação:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Situação');
            $controle->set_array($result);
            $controle->set_valor($parametroSituacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);

            $form->show();

        ##############

            $result = $pessoal->select($select);

            $tabela = new Tabela();   
            $tabela->set_titulo('Relação de Servidores que Ainda NÃO Fizeram o Sispatri');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(array("IdFuncional","Nome","Cargo","Lotação","Situação"));
            $tabela->set_conteudo($result);
            $tabela->set_align(array("center","left","left","left"));
            $tabela->set_classe(array(NULL,NULL,"pessoal","pessoal","pessoal"));
            $tabela->set_metodo(array(NULL,NULL,"get_Cargo","get_Lotacao","get_situacao"));
            
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');
            
            if($numSispatri > 0){
                $tabela->show();
            }else{
                callout("Não há dados para serem exibidos.","secondary");
            }
            
            # Fecha o grid
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
    ################################################################
        
        case "editaServidor" :
            br(8);
            aguarde();
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado',$id);
            
            # Informa a origem
            set_session('origem','areaSispatri.php');
            
            # Carrega a página específica
            loadPage('servidorMenu.php');
            break; 
        
    ################################################################
        
        # Ci
        case "ci" :
                break;
                
    ################################################################
        
        # Importar
        case "importar" :
            
            $grid = new Grid("center");
            $grid->abreColuna(6);

            echo "<form class='upload' method='post' enctype='multipart/form-data'><br>
                    <input type='file' name='sispatri'>
                    <p>Click aqui ou arraste o arquivo.</p>
                    <button type='submit' name='submit'>Upload</button>
                </form>";

            $pasta = "../_temp/";
            
            if ((isset($_POST["submit"])) && (!empty($_FILES['sispatri']))){
                $upload = new Upload($_FILES['sispatri'], $pasta,"sispatri");
                echo $upload->salvar();

                # Registra log
                $Objetolog = new Intra();
                $data = date("Y-m-d H:i:s");
                $atividade = "Alterou a foto do servidor";
                $Objetolog->registraLog($idUsuario,$data,$atividade,NULL,NULL,4);

                # Volta para o menu
                loadPage("?fase=importar1");
            }          
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
            
        case "importar1" :

            br(5);
            aguarde("Apagando a Base Antiga");

            loadPage("?fase=importar2");
            break;
            
        case "importar2" :
            
            # Apaga a tabela
            $select = 'SELECT idSispatri
                         FROM tbsispatri';
                    
            $row = $pessoal->select($select);
            
            $pessoal->set_tabela("tbsispatri");
            $pessoal->set_idCampo("idSispatri");
                        
            foreach ($row as $tt){
                $pessoal->excluir($tt[0]);		
            }
            
            loadPage("?fase=importar3");
            break;
            
        case "importar3" :

            br(5);
            aguarde("Fazendo o upload do arquivo");

            loadPage("?fase=importar4");
            break;
                
            
        case "importar4" :
            # Define o arquivo a ser importado
            $arquivo = "../_temp/sispatri.csv";
            
            # Verifica a existência do arquivo
            if(file_exists($arquivo)){
                $lines = file($arquivo);
                
                # Percorre o arquivo e guarda os dados em um array
                foreach ($lines as $linha) {
                 
                    # Divide as colunas
                    $parte = explode(";",$linha);
                    
                    foreach($parte as $pp) {
                        if(is_numeric($pp)){
                            
                            # Grava na tabela tbsispatri
                            $campos = array("cpf");
                            $valor = array($pp);                    
                            $pessoal->gravar($campos,$valor,NULL,"tbsispatri","idSispatri");
                        }
                    }
                }
            }
            loadPage("?fase=importar5");
            break;
            
        case "importar5" :
            
            br(5);
            aguarde("Vinculando os dados importados<br/>com a base de dados existente.");

            loadPage("?fase=importar6");
            break;
            
        case "importar6" :
            
            $problema = 0;
            
            br();            
            $select = 'SELECT idSispatri,
                              cpf
                         FROM tbsispatri';
                    
            $row = $pessoal->select($select);
            
            $contador = 0;
                        
            foreach ($row as $tt){
                
                $novoCpf = $tt[1];
                $len = strlen($novoCpf);
                
                $novoCpf = str_pad($novoCpf, 11 , "0", STR_PAD_LEFT);
                
                # CPF XXX.XXX.XXX-XX
                
                $parte1 = substr($novoCpf, 0,3);
                $parte2 = substr($novoCpf, 3,3);
                $parte3 = substr($novoCpf, 6,3);
                $parte4 = substr($novoCpf, -2);
                
                $cpfFinalizado = "$parte1.$parte2.$parte3-$parte4";
                
                $select2 = "SELECT idPessoa
                              FROM tbdocumentacao
                             WHERE CPF = '$cpfFinalizado'";
                    
                $row2 = $pessoal->select($select2,FALSE);
                
                if(is_null($row2[0])){
                    $problema++;
                }else{
                    $idServidorPesquisado = $pessoal->get_idServidoridPessoa($row2[0]);
                    
                    # Grava na tabela tbsispatri
                    $campos = array("idServidor");
                    $valor = array($idServidorPesquisado);                    
                    $pessoal->gravar($campos,$valor,$tt[0],"tbsispatri","idSispatri");
                }
            }
            
            if($problema == 0){
                loadPage("?");
            }else{
                echo "problemas $problema";
            }
            break;
        
    ################################################################
        
        
                
    }
            
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}


