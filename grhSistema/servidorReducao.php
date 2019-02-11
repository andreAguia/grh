<?php
/**
 * Controle do Abono de Permanencia
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
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    $reducao = new ReducaoCargaHoraria($idServidorPesquisado);
	
    # Pega o número do processo (Quando tem)
    $processo = trataNulo($reducao->get_numProcesso());
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();
    
    ################################################################
    
    if($fase == "listar"){
        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);
        
        # Cria um menu
        $menu = new MenuBar();

        # Botão voltar
        $linkBotao1 = new Link("Voltar",'servidorMenu.php');
        $linkBotao1->set_class('button');
        $linkBotao1->set_title('Volta para a página anterior');
        $linkBotao1->set_accessKey('V');
        $menu->add_link($linkBotao1,"left");

        if($processo <> "--"){
            # Incluir
            $linkBotao2 = new Link("Incluir",'?fase=editar');
            $linkBotao2->set_class('button');
            $linkBotao2->set_title('Incluir uma nova solicitação de redução');
            $linkBotao2->set_accessKey('I');
            $menu->add_link($linkBotao2,"right");
        }
        
        # Site
        $botaoSite = new Button("Site da GRH");
        $botaoSite->set_target('_blank');
        $botaoSite->set_title("Pagina no site da GRH sobre Abono Permanencia");
        $botaoSite->set_url("http://uenf.br/dga/grh/gerencia-de-recursos-humanos/reducao-de-carga-horaria/");
        $menu->add_link($botaoSite,"right");

        # Legislação
        $botaoLegis = new Button("Legislação");
        $botaoLegis->set_disabled(TRUE);
        $botaoLegis->set_title('Exibe as Legislação pertinente');
        #$botaoLegis->set_onClick("window.open('https://docs.google.com/document/d/e/2PACX-1vRfb7P06MCBHAwd15hKm6KWV4-y0I8yBzlac58uAA-xCHeaL9aCbtSGCgGguZzaPQafvXYvGqWhwG0r/pub','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
        $menu->add_link($botaoLegis,"right");

        # Relatório
        $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
        $botaoRel = new Button();
        $botaoRel->set_imagem($imagem);
        $botaoRel->set_title("Imprimir Relatório de Histórico de Processo de redução da carga horária");
        $botaoRel->set_onClick("window.open('../grhRelatorios/servidorReducao.php','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
        $menu->add_link($botaoRel,"right");
        
        # Fluxograma
        $imagem = new Imagem(PASTA_FIGURAS.'fluxograma.png',NULL,15,15);
        $botaoFluxo = new Button();
        $botaoFluxo->set_imagem($imagem);
        $botaoFluxo->set_title("Exibe o Fluxograma de todo o processo de readaptação");
        $botaoFluxo->set_onClick("window.open('../_diagramas/reducao.png','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=1300,height=700');");
        $menu->add_link($botaoFluxo,"right");
        
        $menu->show();
               
        $objeto->set_botaoVoltarLista(FALSE);
        $objeto->set_botaoIncluir(FALSE);

        $grid->fechaColuna();
        $grid->fechaGrid();
        
        get_DadosServidor($idServidorPesquisado);
    }else{
        # Exibe os dados do Servidor
        $objeto->set_rotinaExtra("get_DadosServidor");
        $objeto->set_rotinaExtraParametro($idServidorPesquisado); 
    }

    ################################################################

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Controle de Redução da Carga Horária');

    # botão de voltar da lista
    $objeto->set_voltarLista('servidorMenu.php');

    # select da lista
    $objeto->set_selectLista('SELECT dtSolicitacao,
                                     dtPericia,
                                     CASE
                                     WHEN resultado = 1 THEN "Deferido"
                                     WHEN resultado = 2 THEN "Indeferido"
                                     ELSE "---"
                                     END,
                                     dtPublicacao,
                                     dtInicio,
                                     periodo,
                                     ADDDATE(dtInicio, INTERVAL periodo MONTH),
                                     numCiInicio,
                                     numCiTermino,
                                     idReducao
                                FROM tbreducao
                               WHERE idServidor = '.$idServidorPesquisado.'
                            ORDER BY dtSolicitacao desc');

    # select do edita
    $objeto->set_selectEdita('SELECT dtSolicitacao,
                                     dtPericia,
                                     resultado,
                                     dtPublicacao,
                                     dtInicio,
                                     periodo,
                                     numCiInicio,
                                     numCiTermino,
                                     idServidor
                                FROM tbreducao
                               WHERE idReducao = '.$id);

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Solicitado em:","Pericia","Resultado","Publicação","Início","Período<br/>(Meses)","Término","CI Início","CI Término"));
    #$objeto->set_width(array(10,10,10,20,20,10,10));	
    $objeto->set_align(array("center"));
    $objeto->set_funcao(array("date_to_php","date_to_php",NULL,"date_to_php","date_to_php",NULL,"date_to_php"));
    
    # Número de Ordem
    $objeto->set_numeroOrdem(TRUE);
    $objeto->set_numeroOrdemTipo("d");

    # Classe do banco de dados
    $objeto->set_classBd('pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbreducao');

    # Nome do campo id
    $objeto->set_idCampo('idReducao');

    # Tipo de label do formulário
    $objeto->set_formLabelTipo(1);

    # Campos para o formulario
    $objeto->set_campos(array( array ( 'nome' => 'dtSolicitacao',
                                       'label' => 'Solicitado em:',
                                       'tipo' => 'data',
                                       'size' => 30,
                                       'required' => TRUE,
                                       'autofocus' => TRUE,
                                       'title' => 'A data da Solicitação.',
                                       'col' => 3,
                                       'fieldset' => 'Da Solicitação',
                                       'linha' => 1),
                               array ( 'nome' => 'dtPericia',
                                       'label' => 'Data do envio a perícia:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'required' => TRUE,
                                       'title' => 'A data do envio do processo à perícia.',
                                       'linha' => 1),
                               array ( 'nome' => 'resultado',
                                       'label' => 'Resultado:',
                                       'tipo' => 'combo',
                                       'array' => array(array(NULL,""),array(1,"Deferido"),array(2,"Indeferido")),
                                       'size' => 20,                               
                                       'title' => 'Se o processo foi deferido ou indeferido',
                                       'col' => 3,
                                       'linha' => 1),
                               array ( 'nome' => 'dtPublicacao',
                                       'label' => 'Data da Publicação:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A Data da Publicação no DOERJ.',
                                       'fieldset' => 'Quando Deferido',
                                       'linha' => 2),
                               array ( 'nome' => 'dtInicio',
                                       'label' => 'Data do Inicio do Benefício:',
                                       'tipo' => 'data',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'A data em que o servidor passou a receber o benefício.',
                                       'linha' => 2),
                               array ( 'nome' => 'periodo',
                                       'label' => 'Período em Meses:',
                                       'tipo' => 'texto',
                                       'size' => 10,
                                       'col' => 3,
                                       'title' => 'O período em meses do benefício.',
                                       'linha' => 2),
                               array ( 'nome' => 'numCiInicio',
                                       'label' => 'CI informando Início:',
                                       'tipo' => 'texto',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Número da Ci informando a chefia imediata do servidor da data de início do benefício.',
                                       'linha' => 3),
                               array ( 'nome' => 'numCiTermino',
                                       'label' => 'CI informando Término:',
                                       'tipo' => 'texto',
                                       'size' => 20,
                                       'col' => 3,
                                       'title' => 'Número da Ci informando a chefia imediata do servidor da data de término do benefício.',
                                       'linha' => 3),
                               array ( 'nome' => 'idServidor',
                                       'label' => 'idServidor',
                                       'tipo' => 'hidden',
                                       'padrao' => $idServidorPesquisado,
                                       'size' => 5,
                                       'linha' => 6)));
    
    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################

    switch ($fase){
        case "" :
        case "listar" :
            # Divide a página em 2 colunas
            $grid = new Grid();
            
            # Verifica status da última solicitação
                
            # Pega os dados
            $select="SELECT dtSolicitacao,
                            dtPericia,
                            resultado,
                            dtPublicacao,
                            dtInicio,
                            periodo,
                            numCiInicio,
                            numCiTermino
                       FROM tbreducao
                      WHERE idServidor = $idServidorPesquisado
                      ORDER BY dtSolicitacao DESC LIMIT 1";

            $dados = $pessoal->select($select,FALSE);
            $numero = $pessoal->count($select);
            $mensagem = NULL;

            # Se foi deferido
            if($dados[2] == 1){
                # Quando não enviou ci de término e a data atual já passou ou é inferior a 90 dias
                if(is_null($dados[7])){

                    if((!is_null($dados[4])) AND (!is_null($dados[5]))){
                        # Variáveis para calculo das datas
                        $dtHoje = date("Y-m-d");
                        $dtInicio = date_to_php($dados[4]);
                        $periodo = $dados[5];
                        $dtTermino = addMeses($dtInicio,$periodo);
                        $dtAlerta = addDias($dtTermino,-90);
                        
                        # Verifica se a data do alerta já passou
                        if(jaPassou($dtAlerta)){
                            $mensagem = "<ul>"
                                      . "<li>Perguntar ao servidor se há interesse em renovação</li>"
                                      . "<li>Enviar CI para o setor do servidor informando o término do benefício</li>"
                                      . "<li>Cadastrar a data de envio da CI de término no sistema</li>"
                                      . "</ul>";
                        }
                    }
                }

                # Quando ainda não enviou a CI de início para a chefia do servidor
                if(is_null($dados[6])){
                    $mensagem = "<ul>"
                              . "<li>Enviar CI para o setor do servidor informando a chefia imediata sobre o benefício concedido</li>"
                              . "<li>Cadastrar o número da CI Inicial no sistema</li>"
                              . "</ul>";
                }

                # Quando ainda não preencheu o período
                if(is_null($dados[5])){
                    $mensagem = "<ul>"
                              . "<li>Cadastrar no sistema o período, em meses,  do benefício</li>"
                              . "</ul>";
                }

                # Quando ainda não preencheu o início do benefício
                if(is_null($dados[4])){
                    $mensagem = "<ul>"
                              . "<li>Cadastrar no sistema o início do benefício</li>"
                              . "</ul>";
                }

                # Quando ainda não foi publicado 
                if(is_null($dados[3])){
                    $mensagem = "<ul>"
                              . "<li>Enviar o processo para o setor de publicação</li>"
                              . "<li>Enviar email ao servidor informando do benefício concedido</li>"
                              . "</ul>";
                }
            }elseif($dados[2] == 2){
                $mensagem = "<ul>"
                          . "<li>Avisar o servidor da negativa</li>"
                          . "<li>Arquivar processo</li>"
                          . "</ul>";
            }

            # Quando ainda não foi informado o resultado 
            if(is_null($dados[2])){
                $mensagem = "<ul>"
                          . "<li>Verificar pelo UPO quando o processo chegar na SPMSO/SES</li>"
                          . "<li>Assim que chegar, avisar o servidor para Enviar email marcando a perícia</li>"
                          . "<li>Aguardar o retorno do processo com o resultado</li>"
                          . "<li>Assim que chegar, cadastrar no sistema o resultado</li>"
                          . "</ul>";
            }


            # Verifica se tem mensagem a ser exibida
            if((!is_null($mensagem)) AND ($numero > 0)){
                $grid->abreColuna(12);
                    $painel = new Callout();
                    $painel->abre();

                        titulotable("Referente à Solicitação de ".date_to_php($dados[0]));
                        p($mensagem);

                    $painel->fecha();            
                $grid->fechaColuna();
            }
                       
            $grid->abreColuna(4);
            
                #$processo = trataNulo($pessoal->get_numProcessoReducao($idServidorPesquisado));
                $painel = new Callout();
                $painel->abre();
                
                    tituloTable("N° do Processo:");
                    br();
                    p($processo,"center");
                    br();
                    
                    $div = new Div("divEditaProcesso");
                    $div->abre();
                        if($processo == "--"){
                            $link = new Link("Incluir Processo",'servidorProcessoReducao.php',"Inclui o número do processo de redução");
                        }else{
                            $link = new Link("Editar Processo",'servidorProcessoReducao.php',"Edita o número do processo de redução");
                        }
                        $link->set_id("editaProcesso");
                        $link->show();
                    $div->fecha();  
                
                $painel->fecha();
                
            $grid->fechaColuna();
            
            $grid->abreColuna(4);
            
                $telefones = $pessoal->get_telefones($idServidorPesquisado);
                
                $painel = new Callout();
                $painel->abre();
                
                    tituloTable("Telefones:");
                    br();
                    p($telefones,"center","f14");                
                
                $painel->fecha();
                
            $grid->fechaColuna();
            
            $grid->abreColuna(4);
            
                $emailPessoal = $pessoal->get_emailPessoal($idServidorPesquisado);
                $emailUenf = $pessoal->get_emailUenf($idServidorPesquisado);
                $emails = NULL;
                
                # Junta os emails
                if(!vazio($emailPessoal)){
                    $emails .= "$emailPessoal<br/>"; 
                }
                
                if(!vazio($emailUenf)){
                    $emails .= "$emailUenf<br/>"; 
                }
                
                $emails = trataNulo($emails);
                
                $painel = new Callout();
                $painel->abre();
                
                    tituloTable("Emails:");
                    br();
                    p($emails,"center","f14");
                                    
                    $div = new Div("divEditaProcesso");
                    $div->abre();
                        $link = new Link("Editar Contatos",'servidorContatos.php',"Edita os contatos do servidor");
                        $link->set_id("editaProcesso");
                        $link->show();
                    $div->fecha();  
                                    
                $painel->fecha();
                
            $grid->fechaColuna();
            
            $grid->fechaGrid();        
            $objeto->listar(); 
            break;
        case "editar" :			
        case "excluir" :
            $objeto->$fase($id); 
            break;

        case "gravar" :
            $objeto->gravar($id);              
            break;

    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}