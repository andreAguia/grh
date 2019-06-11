<?php
/**
 * Cadastro de Servidores
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
    $fase = get('fase');
    $alerta = get('alerta'); // quando vem da rotina de alertas
    
    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou o cadastro de servidores";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega os parâmetros
    $parametroNomeMat = retiraAspas(post('parametroNomeMat',get_session('parametroNomeMat')));
    $parametroCargo = post('parametroCargo',get_session('parametroCargo','*'));
    $parametroCargoComissao = post('parametroCargoComissao',get_session('parametroCargoComissao','*'));
    $parametroLotacao = post('parametroLotacao',get_session('parametroLotacao','*'));
    $parametroPerfil = post('parametroPerfil',get_session('parametroPerfil','*'));
    $parametroSituacao = post('parametroSituacao',get_session('parametroSituacao',1));
    $parametroOrdenacao = post('parametroOrdenacao',get_session('parametroOrdenacao',"3 asc"));
    
    # Agrupamento do Relatório
    $agrupamentoEscolhido = post('agrupamento',0);
    
    # Session do Relatório
    $select = get_session('sessionSelect');
    $titulo = get_session('sessionTitulo');
    $subTitulo = get_session('sessionSubTitulo');
        
    # Joga os parâmetros par as sessions
    set_session('parametroNomeMat',$parametroNomeMat);
    set_session('parametroCargo',$parametroCargo);
    set_session('parametroCargoComissao',$parametroCargoComissao);
    set_session('parametroLotacao',$parametroLotacao);
    set_session('parametroPerfil',$parametroPerfil);
    set_session('parametroSituacao',$parametroSituacao);
    set_session('parametroOrdenacao',$parametroOrdenacao);
    
    # Verifica a paginacão
    $paginacao = get('paginacao',get_session('parametroPaginacao',0));	// Verifica se a paginação vem por get, senão pega a session
    set_session('parametroPaginacao',$paginacao);  

    # Ordem da tabela
    $orderCampo = get('orderCampo');
    $orderTipo = get('orderTipo');
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }
    
    ################################################################
    
    switch ($fase){
        # Lista os Servidores
        case "" :
            br(10);
            aguarde();
            br();
            loadPage('?fase=pesquisar');
            break;
        
        case "pesquisar" :
            # Cadastro de Servidores 
            $grid = new Grid();
            $grid->abreColuna(12);

            # Cria um menu
            $menu1 = new MenuBar();

            # Voltar
            $linkBotao1 = new Link("Voltar","grh.php");
            $linkBotao1->set_class('button');
            $linkBotao1->set_title('Voltar a página anterior');
            $linkBotao1->set_accessKey('V');
            $menu1->add_link($linkBotao1,"left");
            
            # Relatórios
            $imagem = new Imagem(PASTA_FIGURAS.'print.png',NULL,15,15);
            $botaoRel = new Button();
            $botaoRel->set_title("Relatório dessa pesquisa");
            $botaoRel->set_url("?fase=relatorio");
            $botaoRel->set_target("_blank");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel,"right");
                        
            # Novo Servidor
            $linkBotao2 = new Link("Novo Servidor","servidorInclusao.php");
            $linkBotao2->set_class('button');        
            $linkBotao2->set_title('Incluir Novo Servidor');            
            $linkBotao2->set_accessKey('N');
            $menu1->add_link($linkBotao2,"right");
            $menu1->show();
            
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores');
            
            # Parâmetros
            $form = new Form('?');

                # Nome ou Matrícula
                $controle = new Input('parametroNomeMat','texto','Nome, Mat. ou Id:',1);
                $controle->set_size(55);
                $controle->set_title('Nome, matrícula ou ID:');
                $controle->set_valor($parametroNomeMat);
                $controle->set_autofocus(TRUE);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(3);
                $form->add_item($controle);

                # Situação
                $result = $pessoal->select('SELECT idsituacao, situacao
                                              FROM tbsituacao                                
                                          ORDER BY 1');
                array_unshift($result,array('*','-- Todos --'));

                $controle = new Input('parametroSituacao','combo','Situação:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Situação');
                $controle->set_array($result);
                $controle->set_valor($parametroSituacao);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(2);
                $form->add_item($controle);

                # Cargos
                $result1 = $pessoal->select('SELECT tbcargo.idCargo, 
                                                    concat(tbtipocargo.cargo," - ",tbarea.area," - ",tbcargo.nome) as cargo
                                              FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                                           LEFT JOIN tbarea USING (idArea)    
                                      ORDER BY 2');
                
                # cargos por nivel
                $result2 = $pessoal->select('SELECT cargo,cargo FROM tbtipocargo WHERE cargo <> "Professor Associado" AND cargo <> "Professor Titular" ORDER BY 2');
                
                # junta os dois
                $result = array_merge($result2,$result1);
                
                               
                # acrescenta todos
                array_unshift($result,array('*','-- Todos --'));

                $controle = new Input('parametroCargo','combo','Cargo - Área - Função:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Cargo');
                $controle->set_array($result);
                $controle->set_valor($parametroCargo);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(7);
                $form->add_item($controle);

                # Cargos em Comissão
                $result = $pessoal->select('SELECT tbtipocomissao.idTipoComissao,concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao)
                                              FROM tbtipocomissao
                                              WHERE ativo
                                          ORDER BY tbtipocomissao.simbolo');
                array_unshift($result,array('*','-- Todos --'));

                $controle = new Input('parametroCargoComissao','combo','Cargo em Comissão:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Cargo em Comissão');
                $controle->set_array($result);
                $controle->set_valor($parametroCargoComissao);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(2);
                $controle->set_col(3);
                $form->add_item($controle);

                # Lotação
                $result = $pessoal->select('(SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
                array_unshift($result,array('*','-- Todos --'));

                $controle = new Input('parametroLotacao','combo','Lotação:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Lotação');
                $controle->set_array($result);
                $controle->set_valor($parametroLotacao);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(2);
                $controle->set_col(4);
                $form->add_item($controle);

                # Perfil
                $result = $pessoal->select('SELECT idperfil, nome
                                              FROM tbperfil                                
                                          ORDER BY 1');
                array_unshift($result,array('*','-- Todos --'));

                $controle = new Input('parametroPerfil','combo','Perfil:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Perfil');
                $controle->set_array($result);
                $controle->set_valor($parametroPerfil);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(2);
                $controle->set_col(3);
                $form->add_item($controle);
                
                # Ordenação
                $result = $lista->get_ordenacaoCombo();
                
                $controle = new Input('parametroOrdenacao','combo','Ordenado:',1);
                $controle->set_size(30);
                $controle->set_title('Ordenado');
                $controle->set_array($result);
                $controle->set_valor($parametroOrdenacao);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(2);
                $controle->set_col(2);
                $form->add_item($controle);
                
                # submit
                #$controle = new Input('submit','submit');
                #$controle->set_valor('Pesquisar');
                #$controle->set_size(20);
                #$controle->set_accessKey('P');
                #$controle->set_linha(3);
                #$controle->set_col(2);
                #$form->add_item($controle);

                $form->show();
                
                # Paginação
                $lista->set_paginacao(TRUE);
                $lista->set_paginacaoInicial($paginacao);
                $lista->set_paginacaoItens(12);

                if(!vazio($parametroNomeMat)){
                    $lista->set_matNomeId($parametroNomeMat);
                    $lista->set_paginacao(FALSE);
                }
                
                if($parametroCargo <> "*"){
                    $lista->set_cargo($parametroCargo);
                    $lista->set_paginacao(FALSE);
                }

                if($parametroCargoComissao <> "*"){
                    $lista->set_cargoComissao($parametroCargoComissao);
                    $lista->set_paginacao(FALSE);
                }

                if($parametroLotacao <> "*"){
                    $lista->set_lotacao($parametroLotacao);
                    $lista->set_paginacao(FALSE);
                }
                
                if($parametroPerfil <> "*"){
                    $lista->set_perfil($parametroPerfil);
                    $lista->set_paginacao(FALSE);
                }

                if($parametroSituacao <> "*"){
                    $lista->set_situacao($parametroSituacao);
                    if($parametroSituacao <> 1){
                        $lista->set_paginacao(FALSE);
                    }
                }
                
                # Ordenação
                $lista->set_ordenacao($parametroOrdenacao);
                $lista->showTabela();

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
        ###############################

        # Chama o menu do Servidor que se quer editar
        case "editar" :
            br(8);
            aguarde();
            
            # Verifica se veio da rotina de alertas
            if(!is_null($alerta)){
                set_session("origem","alerta");
                set_session("alerta",$alerta);
            }
            
            set_session('idServidorPesquisado',$id);
            loadPage('servidorMenu.php?origem=1');
            break; 
        
        ###############################

        # Cria um relatório com a seleção atual
        case "relatorio" :
            # Lista de Servidores Ativos
            $lista = new ListaServidores('Servidores');
            if($parametroNomeMat <> NULL){
                $lista->set_matNomeId($parametroNomeMat);
            }
            
            if($parametroCargo <> "*"){
                $lista->set_cargo($parametroCargo);
            }

            if($parametroCargoComissao <> "*"){
                $lista->set_cargoComissao($parametroCargoComissao);
            }

            if($parametroLotacao <> "*"){
                $lista->set_lotacao($parametroLotacao);
            }
            
            if($parametroPerfil <> "*"){
                $lista->set_perfil($parametroPerfil);
            }

            if($parametroSituacao <> "*"){
                $lista->set_situacao($parametroSituacao);
            }
            
            $lista->set_ordenacao($parametroOrdenacao);            
            $lista->showRelatorio();
            break; 
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}