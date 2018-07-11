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
    $fase = get('fase');
    
    # Verifica se veio menu grh e registra o acesso no log
    $origem = get('origem',FALSE);
    if($origem){
        # Grava no log a atividade
        $atividade = "Visualizou a área de Licença Prêmio";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    set_session('areaPremio',FALSE);
    
    # Pega os parâmetros
    $parametroNomeMat = post('parametroNomeMat',get_session('parametroNomeMat'));
    $parametroLotacao = post('parametroLotacao',get_session('parametroLotacao'));
    $parametroProcesso = post('parametroProcesso',get_session('parametroProcesso'));
    $parametroSituacao = post('parametroSituacao',get_session('parametroSituacao',1));
    $selectRelatorio = get_session('selectRelatorio');
        
    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat',$parametroNomeMat);
    set_session('parametroLotacao',$parametroLotacao);
    set_session('parametroProcesso',$parametroProcesso);
    set_session('parametroSituacao',$parametroSituacao);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    if($fase <> "relatorio"){
        AreaServidor::cabecalho();
    }
    
################################################################
    
    switch ($fase){
        case "" : 
            br(4);
            aguarde();
            br();
            
            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
                p("Aguarde...","center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=exibeLista');
            break;
        
################################################################
        
        case "exibeLista" :
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
            $botaoRel->set_onClick("window.open('?fase=relatorio','_blank','menubar=no,scrollbars=yes,location=no,directories=no,status=no,width=750,height=600');");
            $botaoRel->set_imagem($imagem);
            $menu1->add_link($botaoRel,"right");

            $menu1->show();

            # Título
            #titulo("Área de Licença Premio");

            ################################################################

            # Formulário de Pesquisa
            $form = new Form('?');

            $controle = new Input('parametroNomeMat','texto','Nome, Matrícula ou id:',1);
            $controle->set_size(100);
            $controle->set_title('Nome do servidor');
            $controle->set_valor($parametroNomeMat);
            $controle->set_autofocus(TRUE);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
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

            # Processo
            $controle = new Input('parametroProcesso','combo','Processo:',1);
            $controle->set_size(30);
            $controle->set_title('Escolhe se tem ou não processo cadastrado');
            $controle->set_array(array("-- Todos --","Cadastrado","Em Branco"));
            $controle->set_valor($parametroProcesso);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(2);
            $form->add_item($controle);

            $form->show();
            
            # Pega o time inicial
            $time_start = microtime(TRUE);
            
            # Conecta com o banco de dados
            $servidor = new Pessoal();

            # Pega os dados
            $select = "SELECT idFuncional,
                              matricula,  
                              tbpessoa.nome,
                              tbservidor.idServidor,
                              concat(IFNULL(tblotacao.UADM,''),' - ',IFNULL(tblotacao.DIR,''),' - ',IFNULL(tblotacao.GER,'')) lotacao,
                              tbservidor.dtAdmissao,
                              tbservidor.processoPremio,
                              tbservidor.idServidor,
                              tbservidor.idServidor,
                              tbsituacao.situacao,
                              tbservidor.idServidor
                         FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idsituacao)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                        WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND idPerfil = 1";
            
            # Matrícula, nome ou id
            if(!is_null($parametroNomeMat)){
                if(is_numeric($parametroNomeMat)){
                    $select .= ' AND ((';
                }else{
                    $select .= ' AND (';
                }

                $select .= 'tbpessoa.nome LIKE "%'.$parametroNomeMat.'%")';

                if(is_numeric($parametroNomeMat)){
                    $select .= ' OR (tbservidor.matricula LIKE "%'.$parametroNomeMat.'%")
                                 OR (tbservidor.idfuncional LIKE "%'.$parametroNomeMat.'%"))';        
                }
            }
            
            # Lotação
            if(($parametroLotacao <> "*") AND ($parametroLotacao <> "")){
                if(is_numeric($parametroLotacao)){
                    $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
                }else{ # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")';
                }
            }
            
            # Processo
            switch ($parametroProcesso){
                case "Cadastrado":
                    $select .= ' AND tbservidor.processoPremio IS NOT NULL';
                    break;
                
                case "Em Branco":
                    $select .= ' AND tbservidor.processoPremio IS NULL';
                    break;
            }
            
            # situação
            if(($parametroSituacao <> "*") AND ($parametroSituacao <> "")){
                $select .= ' AND (tbservidor.situacao = "'.$parametroSituacao.'")';                
            }        

            $select .= "  ORDER BY tbpessoa.nome";
            
            # Guarde o select para o relatório
            set_session('selectRelatorio',$select);
            
            $resumo = $servidor->select($select);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Id","Matrícula","Nome","Cargo","Lotação","Admissão","Processo","Número de Dias<br/>Publ./ Fruídos / Disp.","Número de Publicações<br/>Reais / Possíveis / Faltantes","Situação"));
            $tabela->set_align(array("center","center","left","left","left","center","left"));
            #$tabela->set_width(array(5,15,15,15,8,15,15,15));
            $tabela->set_funcao(array(NULL,"dv",NULL,NULL,NULL,"date_to_php",NULL,"exibeDiasLicencaPremio","exibeNumPublicacoesLicencaPremio"));
            $tabela->set_classe(array(NULL,NULL,NULL,"pessoal"));
            $tabela->set_metodo(array(NULL,NULL,NULL,"get_Cargo"));
            $tabela->set_titulo("Licença Prêmio");
            
            if(!is_null($parametroNomeMat)){
                $tabela->set_textoRessaltado($parametroNomeMat);
            }
            
            $tabela->set_editar('?fase=editaServidorPremio&id=');
            $tabela->set_nomeColunaEditar("Acessar");
            $tabela->set_editarBotao("ver.png");
            $tabela->set_idCampo('idServidor');
            $tabela->show();
            
            # Pega o time final
            $time_end = microtime(TRUE);
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',')." segundos","right","f10");
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
################################################################

        # Chama o menu do Servidor que se quer editar
        case "editaServidorPremio" :
            set_session('idServidorPesquisado',$id);
            set_session('areaPremio',TRUE);
            loadPage('servidorLicencaPremio.php');
            break; 
        
################################################################

        # Relatório
        case "relatorio" :
            $result = $pessoal->select($selectRelatorio);
            
            # Inicia a variável do subtítulo
            $subtitulo = NULL;
            
            # Lotação
            if(($parametroLotacao <> "*") AND ($parametroLotacao <> "")){
                $subtitulo = $pessoal->get_nomeLotacao($parametroLotacao)."<br/>";
            }
            
            # Processo
            switch ($parametroProcesso){
                case "Cadastrado":
                    $subtitulo .= "Processos Cadastrados<br/>";
                    break;
                
                case "Em Branco":
                    $subtitulo .= "Processos Em Branco<br/>";
                    break;
            }
            
            # Situação
            if(($parametroSituacao <> "*") AND ($parametroSituacao <> "")){
                $subtitulo .= "Servidores ".$pessoal->get_nomeSituacao($parametroSituacao)."s<br/>";
            }
            
            # Nome, MAtricula e id
            if(!is_null($parametroNomeMat)){
                $subtitulo .= "Pesquisa: ".$parametroNomeMat;
            }

            $relatorio = new Relatorio();
            $relatorio->set_titulo('Relatório de Licença Prêmio');
            
            # Acrescenta o subtítulo de tiver filtro
            if($subtitulo <> NULL){
                $relatorio->set_subtitulo($subtitulo);
            }
            
            $relatorio->set_label(array("Id","Matrícula","Nome","Cargo","Lotação","Admissão","Processo","Número de Dias<br/>Publ./ Fruídos / Disp.","Número de Publicações<br/>Reais / Possíveis / Faltantes","Situação"));
            $relatorio->set_align(array("center","center","left","left","left","center","left"));
            $relatorio->set_funcao(array(NULL,"dv",NULL,NULL,NULL,"date_to_php",NULL,"exibeDiasLicencaPremio","exibeNumPublicacoesLicencaPremio"));
            $relatorio->set_classe(array(NULL,NULL,NULL,"pessoal"));
            $relatorio->set_metodo(array(NULL,NULL,NULL,"get_Cargo"));

            $relatorio->set_conteudo($result);
            $relatorio->show();
            break; 
        
################################################################
        
    }
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}


