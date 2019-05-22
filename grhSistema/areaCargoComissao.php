<?php
/**
 * Estatística
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
    $fase = get('fase','inicial');
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Verifica o post de quando exibe os histórico de servidores nesse cargo
    $parametroCargo = get('parametroCargo',get_session('parametroCargo',13));
    $parametroDescricao = post('parametroDescricao',get_session('parametroDescricao'));
    $parametroStatus = post('parametroStatus',get_session('parametroStatus',"Vigente"));
    $parametroAno = post('parametroAno',get_session('parametroAno',date("Y")));
    $parametroMes = post('parametroMes',get_session('parametroMes',date('m')));
    
    # Joga os parâmetros par as sessions    
    set_session('parametroCargo',$parametroCargo);
    set_session('parametroDescricao',$parametroDescricao);
    set_session('parametroStatus',$parametroStatus);
    set_session('parametroAno',$parametroAno);
    set_session('parametroMes',$parametroMes);
    
    # Começa uma nova página
    $page = new Page();        
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Limita o tamanho da tela
    $grid1 = new Grid();
    $grid1->abreColuna(12);

    # Cria um menu
    $menu1 = new MenuBar();

    # Voltar
    if($fase == "inicial"){
        $linkVoltar = new Link("Voltar","grh.php");
    }else{
        $linkVoltar = new Link("Voltar","?");
    }
    $linkVoltar->set_class('button');
    $linkVoltar->set_title('Voltar para página anterior');
    $linkVoltar->set_accessKey('V');
    $menu1->add_link($linkVoltar,"left");

    $menu1->show();
    
    titulo("Cargo em Comissão");
    br();
    
    switch ($fase){
        case "inicial":
            $grid = new Grid();

            ####################################
            ## Menu Lateral
            ####################################
            
            $grid->abreColuna(12,3);

                # Inicia o Menu de Cadastro
                $menu = new Menu();
                $menu->add_item("titulo","Cadastro de Cargos");
                $menu->add_item("link","Cadastro de Cargos em Comissão","cadastroCargoComissao.php");
                $menu->add_item("link","Nomeações & Exonerações por Mês","?fase=movimentacao");   
                $menu->show();

                # Inicia o Menu de Relatório
                $menu = new Menu();
                $menu->add_item('titulo','Relatórios');
                $menu->add_item('linkWindow','Planilhão Histórico','../grhRelatorios/cargoComissaoPlanilhaoHistorico.php');
                $menu->add_item('linkWindow','Planilhão Vigente','../grhRelatorios/cargoComissaoPlanilhaoVigente.php');
                $menu->show();

                # Pega os cargos
                $select = "SELECT idTipoComissao,
                                  descricao,
                                  simbolo,
                                  valSal
                                  FROM tbtipocomissao
                                 WHERE ativo = TRUE
                              ORDER BY simbolo, descricao";
                $row = $pessoal->select($select);

                # Inicia o Menu de Cargos
                $menu = new Menu();
                $menu->add_item('titulo','Servidores por Cargos');

                # Preenche com os cargos
                foreach($row as $item){
                    if($parametroCargo == $item[0]){
                        $menu->add_item('link','<b>'.$item[2].' - '.$item[1].'</b>','?fase=inicial&parametroCargo='.$item[0]);
                    }else{
                        $menu->add_item('link',$item[2].' - '.$item[1],'?fase=inicial&parametroCargo='.$item[0]);
                    }
                }        
                $menu->show();

            $grid->fechaColuna();

            ####################################
            ## Área central de conteúdo
            ####################################
            
            $grid->abreColuna(12,9);
            
            $form = new Form('?');

            # Descrição    
            $controle = new Input('parametroDescricao','texto','Descrição do Cargo ou Nome do Servidor:',1);
            $controle->set_size(200);
            $controle->set_title('Filtra por Descrição');
            $controle->set_valor($parametroDescricao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_autofocus(TRUE);
            $controle->set_col(9);                
            $form->add_item($controle);
            
             # Status    
            $controle = new Input('parametroStatus','combo','Status',1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Status');
            $controle->set_array(array("Vigente","Todos"));
            $controle->set_valor($parametroStatus);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);            
            $form->add_item($controle);

            $form->show();
                        
            # Pega o nome do cargo            
            $nomeCargo = $pessoal->get_nomeCargoComissao($parametroCargo);
            $simbolo = $pessoal->get_cargoComissaoSimbolo($parametroCargo);
                        
            # select
            $select ='SELECT tbservidor.idServidor,
                             tbpessoa.nome,
                             tbcomissao.dtNom,
                             tbcomissao.dtExo,
                             tbcomissao.idComissao,
                             idPerfil,
                             concat(tbtipocomissao.simbolo," - ",tbtipocomissao.descricao),
                             idComissao
                        FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                        LEFT JOIN tbcomissao ON(tbservidor.idServidor = tbcomissao.idServidor)
                                             JOIN tbtipocomissao ON(tbcomissao.idTipoComissao=tbtipocomissao.idTipoComissao)
                       WHERE tbtipocomissao.idTipoComissao = '.$parametroCargo;
            
            if(($parametroCargo <> 13) AND ($parametroCargo <> 14) AND ($parametroCargo <> 23) AND ($parametroCargo <> 25) AND ($parametroCargo <> 18)){
                if(!vazio($parametroDescricao)){
                    $select .= " AND tbcomissao.descricao LIKE '%".$parametroDescricao."%'";
                }
            }
            
            if($parametroStatus == "Vigente"){
                $select .= " AND (tbcomissao.dtExo IS NULL OR CURDATE() < tbcomissao.dtExo)";
            }
            
            # Pega o parâmetro da pesquisa
            #if(!is_null($parametroDescricao)){
            #    $select .= ' AND (tbcomissao.descricao LIKE "%'.$parametroDescricao.'%"';
            #    $select .= ' OR tbpessoa.nome LIKE "%'.$parametroDescricao.'%")';
            #}
            
            $select .= ' ORDER BY tbcomissao.descricao, tbcomissao.dtNom desc';

            $result = $pessoal->select($select);
            $label = array('Id / Matrícula','Nome','Nomeação','Exoneração','Nome do Cargo','Perfil');
            $align = array("center","left","center","center","left","center");
            $function = array("idMatricula",NULL,"date_to_php","date_to_php","descricaoComissao");
            $classe = array(NULL,NULL,NULL,NULL,NULL,"Pessoal");
            $metodo = array(NULL,NULL,NULL,NULL,NULL,"get_perfil");
           
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label($label);
            $tabela->set_titulo("$nomeCargo [$simbolo]");
            $tabela->set_align($align);
            $tabela->set_funcao($function);
            $tabela->set_classe($classe);
            $tabela->set_metodo($metodo);
            $tabela->set_idCampo('idComissao');
            $tabela->set_editar('?fase=editarCargo');
            $tabela->set_formatacaoCondicional(array( array('coluna' => 3,
                                                    'valor' => NULL,
                                                    'operador' => '=',
                                                    'id' => 'vigente')));
            $tabela->show();
            break;
            
    ################################################################
            
        case "movimentacao":
            
            $form = new Form('?fase=movimentacao');
            $controle = new Input('parametroAno','texto','Ano:',1);
            $controle->set_size(8);
            $controle->set_title('Filtra pelo Ano');
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_autofocus(TRUE);
            $controle->set_col(3);
            $form->add_item($controle);
            
            $controle = new Input('parametroMes','combo','Mês:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra pelo Mês');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            
            $form->show();
            
            # Nomeações
            $select = 'SELECT tbservidor.idFuncional,
                              tbpessoa.nome,
                              CONCAT(simbolo," - ",tbtipocomissao.descricao),
                              tbcomissao.dtNom,
                              tbcomissao.dtPublicNom,
                              tbcomissao.dtAtoNom,
                              tbcomissao.numProcNom,
                              tbcomissao.idComissao
                         FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
                                         JOIN tbservidor USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                         WHERE YEAR(tbcomissao.dtNom) = '.$parametroAno.' 
                           AND MONTH(tbcomissao.dtNom) = '.$parametroMes.' 
                     ORDER BY tbcomissao.dtNom';
            
            $result = $pessoal->select($select);
            $label = array('Id Funcional','Nome','Cargo','Nomeação','Publicação','Ato Reitor','Processo');
            $align = array("center","left","left","center","center","center","left");
            $function = array(NULL,NULL,NULL,"date_to_php","date_to_php","date_to_php");
            #$classe = array(NULL,NULL,NULL,NULL,NULL,"Pessoal");
            #$metodo = array(NULL,NULL,NULL,NULL,NULL,"get_perfil");
            
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label($label);
            $tabela->set_titulo("Nomeações");
            $tabela->set_align($align);
            $tabela->set_funcao($function);
            $tabela->set_idCampo('idComissao');
            $tabela->set_editar('?fase=editarCargo2');
            $tabela->show();
                        
            # Exonerações
            $select = 'SELECT tbservidor.idFuncional,
                              tbpessoa.nome,
                              CONCAT(simbolo," - ",tbtipocomissao.descricao),
                              tbcomissao.dtExo,
                              tbcomissao.dtPublicExo,
                              tbcomissao.dtAtoExo,
                              tbcomissao.numProcExo,
                              tbcomissao.idComissao
                         FROM tbcomissao JOIN tbtipocomissao USING (idTipoComissao)
                                         JOIN tbservidor USING (idServidor)
                                         JOIN tbpessoa USING (idPessoa)
                         WHERE YEAR(tbcomissao.dtExo) = '.$parametroAno.' 
                           AND MONTH(tbcomissao.dtExo) = '.$parametroMes.' 
                     ORDER BY tbcomissao.dtExo';
            
            $result = $pessoal->select($select);
            $label = array('Id Funcional','Nome','Cargo','Exoneração','Publicação','Ato Reitor','Processo');
            $align = array("center","left","left","center","center","center","left");
            $function = array(NULL,NULL,NULL,"date_to_php","date_to_php","date_to_php");
            
            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_label($label);
            $tabela->set_titulo("Exonerações");
            $tabela->set_align($align);
            $tabela->set_funcao($function);
            $tabela->set_idCampo('idComissao');
            $tabela->set_editar('?fase=editarCargo2');
            $tabela->show();
            break;
            
    ################################################################
        
        case "editarCargo" :
            # Vigentes
            br(8);
            aguarde();
            
            $comissao = new CargoComissao();
            $dados = $comissao->get_dados($id);
            $idServidor = $dados["idServidor"];
            $idTipoComissao = $dados["idTipoComissao"];
            
            # Informa o idComissao
            set_session("comissao",$idTipoComissao);
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado',$idServidor);
            
            # Informa a origem
            set_session('origem','areaCargo');
            
            # Carrega a página específica
            loadPage('servidorComissao.php?fase=editar&id='.$id);
            break; 
        
################################################################
        
        case "editarCargo2" :
            # Vigentes
            br(8);
            aguarde();
            
            $comissao = new CargoComissao();
            $dados = $comissao->get_dados($id);
            $idServidor = $dados["idServidor"];
            $idTipoComissao = $dados["idTipoComissao"];
            
            # Informa o idComissao
            set_session("comissao",$idTipoComissao);
            
            # Informa o $id Servidor
            set_session('idServidorPesquisado',$idServidor);
            
            # Informa a origem
            set_session('origem','areaCargo2');
            
            # Carrega a página específica
            loadPage('servidorComissao.php?fase=editar&id='.$id);
            break; 
        
################################################################
    }
    
    # Fecha o grid
    $grid1->fechaColuna();
    $grid1->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}