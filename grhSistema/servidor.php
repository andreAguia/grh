<?php
/**
 * Cadastro de Servidores
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = null;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
    
    # Verifica se foi pesquisado
    $pesquisado = get('pesquisado',FALSE);        
	
    # Verifica a fase do programa
    $fase = get('fase');

    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega os parâmetros
    $parametroNomeMat = retiraAspas(post('parametroNomeMat',get_session('parametroNomeMat')));
    $parametroCargo = post('parametroCargo',get_session('parametroCargo','*'));
    $parametroCargoComissao = post('parametroCargoComissao',get_session('parametroCargoComissao','*'));
    $parametroLotacao = post('parametroLotacao',get_session('parametroLotacao','*'));
    $parametroPerfil = post('parametroPerfil',get_session('parametroPerfil','*'));
    $parametroSituacao = post('parametroSituacao',get_session('parametroSituacao',1)); 
    
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
    
    switch ($fase)
    {
        # Lista os Servidores
        case "" :
            br(10);
            mensagemAguarde();
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
            $linkBotao3 = new Link("Imprimir");
            $linkBotao3->set_class('button');        
            $linkBotao3->set_title('Relatório dessa pesquisa');
            $linkBotao3->set_onClick("window.open('?fase=relatorio','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
            $linkBotao3->set_accessKey('I');
            $menu1->add_link($linkBotao3,"right");

            $menu1->show();

            # Parâmetros
            $form = new Form('?pesquisado=TRUE');

                # Nome ou Matrícula
                $controle = new Input('parametroNomeMat','texto','Nome, matrícula ou IdFuncional:',1);
                $controle->set_size(55);
                $controle->set_title('Nome, matrícula ou ID:');
                $controle->set_valor($parametroNomeMat);
                $controle->set_autofocus(true);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(4);
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
                $result = $pessoal->select('SELECT tbcargo.idCargo,
                                                   concat(tbtipocargo.cargo," - ",tbcargo.nome)
                                              FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)                              
                                          ORDER BY 2,1');
                array_unshift($result,array('*','-- Todos --'));

                $controle = new Input('parametroCargo','combo','Cargo:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Cargo');
                $controle->set_array($result);
                $controle->set_valor($parametroCargo);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(1);
                $controle->set_col(6);
                $form->add_item($controle);

                # Cargos em Comissão
                $result = $pessoal->select('SELECT tbtipocomissao.descricao,tbtipocomissao.descricao
                                              FROM tbtipocomissao                                
                                          ORDER BY 1');
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
                $result = $pessoal->select('SELECT idlotacao, concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                              FROM tblotacao                                
                                          ORDER BY ativo desc,lotacao');
                array_unshift($result,array('*','-- Todos --'));

                $controle = new Input('parametroLotacao','combo','Lotação:',1);
                $controle->set_size(30);
                $controle->set_title('Filtra por Lotação');
                $controle->set_array($result);
                $controle->set_valor($parametroLotacao);
                $controle->set_onChange('formPadrao.submit();');
                $controle->set_linha(2);
                $controle->set_col(6);
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

                $form->show();

                # Lista de Servidores Ativos
                $lista = new listaServidores('Servidores');
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
                
                # Paginação
                $lista->set_paginacao(true);
                $lista->set_paginacaoInicial($paginacao);
                $lista->set_paginacaoItens(12);
                $lista->show();
                
                # Pega o select atualizado
                $select = $lista->get_select();
                $subTitulo = $lista->get_subTitulo();
                $titulo = $lista->get_titulo();

                # Retira o LIMIT que ajuda na paginação
                # (no relatório não tem paginação)
                $palavraProcurada = 'LIMIT';                     // Define a palavra do select qu ser´aprocurada
                $posicao = stripos($select, $palavraProcurada);  // Verifica se ela está no select

                # Envia o select por session
                if($posicao === FALSE){   // Verifica a posição. o === é necessário para não confundir o false com o 0
                    set_session('sessionSelect',$select);  
                }else{
                    set_session('sessionSelect',strstr($select, 'LIMIT', true));
                }

                # Envia o Título por session
                set_session('sessionSubTitulo',$subTitulo); 
                set_session('sessionTitulo',$titulo);

            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
        ###############################

        # Chama o menu do Servidor que se quer editar
        case "editar" :
            br(8);
            mensagemAguarde();
            set_session('idServidorPesquisado',$id);
            loadPage('servidorMenu.php');
            break; 
        
        ###############################

        # Cria um relatório com a seleção atual
        case "relatorio" :
            $lista = new listaServidores('Servidores');
            $lista->relatorio($select,$titulo,$subTitulo);
            break; 
    }
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}