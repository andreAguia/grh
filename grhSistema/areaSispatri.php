<?php
/**
 * Área de Licença Prêmio
 *  
 * By Alat
 */


#
#       ATENÇÃO !!
#       Esta rotina usa a classe upload que foi descontinuada
#       Para reativá-la deve-se fazer a conversão para as novas rotinas !!
#
#

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
    
    # Joga os parâmetros par as sessions   
    set_session('parametroLotacao',$parametroLotacao);
    
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

    # Ci
    $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
    $botaoRel = new Button();
    $botaoRel->set_title("CI");
    $botaoRel->set_url("../grhRelatorios/ciSispatri.php");
    $botaoRel->set_target("_blank");
    $botaoRel->set_imagem($imagem);
    
    if($parametroLotacao <> "Todos"){
        $menu1->add_link($botaoRel,"right");
    }
    
    # Importar
    $botaoImp = new Link("Importar","?fase=importar");
    $botaoImp->set_class('button');
    $botaoImp->set_title('Importa arquivo cvs');
    $botaoImp->set_accessKey('I');
    $menu1->add_link($botaoImp,"right");

    $menu1->show();

    # Titulo
    titulo("Área do Sispatri");
    br();
    
    # Inicia a Classe
    $sispatri = new Sispatri();
    $sispatri->set_lotacao($parametroLotacao);
            
################################################################
    
    switch ($fase){
        
        # Área Lateral
        case "resumo" :
            
            # Pega o Número de Servidores
            $numSispatriAtivos = $sispatri->get_numServidoresAtivos();
            $numSispatriNaoAtivos = $sispatri->get_numServidoresNaoAtivos();
            
            $grid = new Grid();

            ## Coluna do menu            
            $grid->abreColuna(12,3);
            
                # Número de Servidores
                $painel = new Callout();
                $painel->abre();

                titulo("Resumo");
                br();

                $texto1 = Null;             

                if($parametroLotacao == "Todos"){
                    $numServidores = $pessoal->get_numServidoresAtivos();
                    $texto1 = "Na UENF";
                }else{
                    $numServidores = $pessoal->get_numServidoresAtivos($parametroLotacao);
                    $texto1 = "Nesta Lotação";
                }
                
                $array = array(
                    array("Ativos",$numSispatriAtivos),
                    array("Não Ativos",$numSispatriNaoAtivos,),
                    array("Total",$numSispatriAtivos+$numSispatriNaoAtivos)
                    );
                
                $tabela = new Tabela();
                $tabela->set_titulo("Não Fizeram o Sispatri");
                $tabela->set_conteudo($array);
                $tabela->set_label(array("Descrição","Servidores"));
                $tabela->set_align(array("left","center"));
                $tabela->set_totalRegistro(FALSE);
                $tabela->set_formatacaoCondicional(array( array('coluna' => 0,
                                                'valor' => "Total",
                                                'operador' => '=',
                                                'id' => 'estatisticaTotal')));
                $tabela->show();

                # Chart
                $chart = new Chart("Pie",array(array("Fez Sispatri",$numServidores-$numSispatriAtivos),array("Não Fez Sispatri",$numSispatriAtivos)));
                $chart->set_idDiv("sispatri");
                $chart->set_legend(FALSE);
                $chart->set_tamanho($largura = "50%",$altura = "50%");
                $chart->show();

                $array = array(
                    array($texto1,$numServidores),
                    array("Fizeram o Sispatri",$numServidores - $numSispatriAtivos),
                    array("Não Fizeram o Sispatri",$numSispatriAtivos)
                    );
                
                $tabela = new Tabela();
                $tabela->set_titulo("Servidores Ativos");
                $tabela->set_conteudo($array);
                $tabela->set_label(array("Descrição","Servidores"));
                $tabela->set_align(array("left","center"));
                $tabela->set_totalRegistro(FALSE);
                $tabela->show();
                
                $painel->fecha();
            
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
            $controle->set_col(12);
            $form->add_item($controle);

            $form->show();

        ##############

            $result = $sispatri->get_servidoresAtivos();

            $tabela = new Tabela();   
            $tabela->set_titulo('Servidores Ativos que Não Fizeram Sispatriff');
            #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
            $tabela->set_label(array("IdFuncional","Nome","Cargo","Lotação","Situação"));
            $tabela->set_conteudo($result);
            $tabela->set_align(array("center","left","left","left"));
            $tabela->set_classe(array(NULL,NULL,"pessoal","pessoal"));
            $tabela->set_metodo(array(NULL,NULL,"get_Cargo","get_Lotacao"));
            $tabela->set_funcao(array(NULL,NULL,NULL,NULL,"get_situacao"));
            
            $tabela->set_idCampo('idServidor');
            $tabela->set_editar('?fase=editaServidor');
            
            if($numSispatriAtivos > 0){
                $tabela->show();
            }else{
                callout("Não há dados para serem exibidos.","secondary");
            }
            
         #######
            
            if($numSispatriNaoAtivos > 0){
                $result = $sispatri->get_servidoresNaoAtivos();

                $tabela = new Tabela();   
                $tabela->set_titulo('Servidores Não Ativos que Não Fizeram Sispatri');
                #$tabela->set_subtitulo('Filtro: '.$relatorioParametro);
                $tabela->set_label(array("IdFuncional","Nome","Cargo","Lotação","Situação"));
                $tabela->set_conteudo($result);
                $tabela->set_align(array("center","left","left","left"));
                $tabela->set_classe(array(NULL,NULL,"pessoal","pessoal","pessoal"));
                $tabela->set_metodo(array(NULL,NULL,"get_Cargo","get_Lotacao","get_situacao"));

                $tabela->set_idCampo('idServidor');
                $tabela->set_editar('?fase=editaServidor');

                $tabela->show();
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
            
            loadPage("?");
            break;
        
    ################################################################
        
        
                
    }
            
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}


