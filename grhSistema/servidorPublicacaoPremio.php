<?php
/**
 * Cadastro de Publicação de Licenças Prêmios
 *  
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','listar');

    # Pega o parametro de pesquisa (se tiver)
    if (is_null(post('parametro')))					# Se o parametro n?o vier por post (for nulo)
        $parametro = retiraAspas(get_session('sessionParametro'));	# passa o parametro da session para a variavel parametro retirando as aspas
    else
    { 
        $parametro = post('parametro');                # Se vier por post, retira as aspas e passa para a variavel parametro
        set_session('sessionParametro',$parametro);    # transfere para a session para poder recuperá-lo depois
    }

    # Ordem da tabela
    $orderCampo = get('orderCampo',1);
    $orderTipo = get('orderTipo','asc');
    
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
    
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado); 

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Publicação de Licença Prêmio no DOERJ');

    # botão salvar
    $objeto->set_botaoSalvarGrafico(false);

    # bot?o de voltar da lista
    $objeto->set_voltarLista('servidorLicenca.php');

    # controle de pesquisa
    #$objeto->set_parametroLabel('Pesquisar');
    #$objeto->set_parametroValue($parametro);

    # select da lista
    $objeto->set_selectLista ('SELECT dtPublicacao,
                                      pgPublicacao,
                                      dtInicioPeriodo,
                                      dtFimPeriodo,                                  
                                      processo,
                                      numDias,
                                      idPublicacaoPremio,
                                      idPublicacaoPremio
                                 FROM tbpublicacaoPremio
                                 WHERE idServidor = '.$idServidorPesquisado.'
                             ORDER BY '.$orderCampo.' '.$orderTipo);

    # select do edita
    $objeto->set_selectEdita('SELECT dtPublicacao,
                                     pgPublicacao,
                                     processo,
                                     dtInicioPeriodo,
                                     dtFimPeriodo,
                                     numDias,
                                     obs,
                                     idServidor
                                FROM tbpublicacaoPremio
                               WHERE idpublicacaoPremio = '.$id);

    # ordem da lista
    $objeto->set_orderCampo($orderCampo);
    $objeto->set_orderTipo($orderTipo);
    $objeto->set_orderChamador('?fase=listar');

    # Caminhos
    $objeto->set_linkEditar('?fase=editar');
    $objeto->set_linkExcluir('?fase=excluir');
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('?fase=listar');

    # Parametros da tabela
    $objeto->set_label(array("Data da Publicação","Pag.","Período Aquisitivo - Início","Período Aquisitivo - Fim","Processo","Dias Publicados","Dias Fruídos","Disponíveis"));
    $objeto->set_width(array(10,5,14,14,20,8,8,8));
    $objeto->set_align(array("center"));
    $objeto->set_function(array('date_to_php',null,'date_to_php','date_to_php',null));
    $objeto->set_classe(array(null,null,null,null,null,null,'Pessoal','Pessoal'));
    $objeto->set_metodo(array(null,null,null,null,null,null,'get_licencaPremioNumDiasFruidasPorId','get_licencaPremioNumDiasDisponiveisPorId'));
    $objeto->set_numeroOrdem(true);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbpublicacaoPremio');

    # Nome do campo id
    $objeto->set_idCampo('idPublicacaoPremio');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Foco do form
    $objeto->set_formFocus('dtPublicacao');

    # Campos para o formulario
    $objeto->set_campos(array(
                        array ( 'nome' => 'dtPublicacao',
                                'label' => 'Data da Pub. no DOERJ:',
                                'tipo' => 'data',
                                'size' => 20,
                                'col' => 3,
                                'required' => true,
                                'title' => 'Data da Publicação no DOERJ.',
                                'linha' => 1),
                        array ( 'nome' => 'pgPublicacao',
                                'label' => 'Pág:',
                                'tipo' => 'texto',
                                'col' => 2,
                                'size' => 5,                         
                                'title' => 'A Página do DOERJ',
                                'linha' => 1),
                        array ( 'nome' => 'processo',
                                'label' => 'Processo:',
                                'tipo' => 'texto',
                                'padrao' => $pessoal->get_licencaPremioNumProcesso($idServidorPesquisado),
                                'size' => 30,
                                'col' => 4,
                                'required' => true,
                                'title' => 'Número do Processo',
                                'linha' => 1),
                        array ( 'nome' => 'dtInicioPeriodo',
                                'label' => 'Período Aquisitivo Início:',
                                'tipo' => 'data',
                                'col' => 3,
                                'size' => 20,
                                'required' => true,                 
                                'title' => 'Data de início do período aquisitivo',
                                'linha' => 2),
                        array ( 'nome' => 'dtFimPeriodo',
                                'label' => 'Período Aquisitivo Término:',
                                'tipo' => 'data',
                                'size' => 20,
                                'col' => 3,
                                'required' => true,                 
                                'title' => 'Data de término do período aquisitivo',
                                'linha' => 2),
                        array ( 'nome' => 'numDias',
                                'label' => 'Dias:',
                                'tipo' => 'numero',
                                'padrao' => 90,
                                'size' => 5,
                                'col' => 3,
                                'required' => true,
                                'title' => 'Dias de Férias.',
                                'linha' => 2),
                         array ('linha' => 5,
                                'nome' => 'obs',
                                'label' => 'Observação:',
                                'tipo' => 'textarea',
                                'linha' => 3,
                                'col' => 12,
                                'size' => array(80,5)),
                        array ( 'nome' => 'idServidor',
                                'label' => 'idServidor:',
                                'tipo' => 'hidden',
                                'padrao' => $idServidorPesquisado,
                                'size' => 5,
                                'title' => 'Matrícula',
                                'linha' => 6)));

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);

    ################################################################
    switch ($fase)
    {
            case "" :
            case "listar" : 
                # Exibe quadro de licença prêmio
                Grh::quadroLicencaPremio($idServidorPesquisado);

                $objeto->listar();
                
                # Exibe as licenças prêmio
                $select = 'SELECT tbtipolicenca.nome,
                                     dtPublicacao,
                                     pgPublicacao,
                                     dtInicial,
                                     numdias,
                                     ADDDATE(dtInicial,numDias-1),
                                     tblicenca.processo,
                                     dtInicioPeriodo,
                                     dtFimPeriodo                                     
                                FROM tblicenca LEFT JOIN tbtipolicenca ON tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca
                               WHERE tblicenca.idTpLicenca = 6 AND idServidor='.$idServidorPesquisado.'
                            ORDER BY tblicenca.dtInicial';

                $result = $pessoal->select($select);
                $count = $pessoal->count($select);

                # Cabeçalho da tabela
                $titulo = 'Licenças Prêmio';
                $label = array("Licença","Publicação","Pag.","Inicio","Dias","Término","Processo","Período Aquisitivo Início","Período Aquisitivo Término");
                $width = array(13,10,6,10,6,10,15,15,15);
                $funcao = array(null,'date_to_php',null,'date_to_php',null,'date_to_php',null,'date_to_php','date_to_php');
                $align = array('center');

                # Exibe a tabela
                $tabela = new Tabela();
                $tabela->set_conteudo($result);
                $tabela->set_cabecalho($label,$width,$align);
                $tabela->set_titulo($titulo);
                $tabela->set_funcao($funcao);
                
                # Limita o tamanho da tela
                $grid = new Grid();
                $grid->abreColuna(12);
    
                $tabela->show();
                
                $grid->fechaColuna();
                $grid->fechaGrid();   
                break;

            case "editar" :	
            case "gravar" :		
                $objeto->$fase($id);
                break;

            case "excluir" :
                # verifica se tem licenças cadastradas com essa publicação antes de excluir
                $numLicencas = $pessoal->get_LicencaPremioNumPublicacao($id);
                if($numLicencas <= 0)
                    $objeto->excluir($id);
                else
                    Alert::alert ('Essa publicação não pode ser excluída pois existe(m) '.$numLicencas.' licença(s) cadastrada(s) com essa publicação!!');
                    back(1);
                break;
    }									 	 		

    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}