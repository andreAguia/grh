<?php
/**
 * Área de Afastamentos da GRH
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
    $grh = get('grh',FALSE);
    if($grh){
        # Grava no log a atividade
        $atividade = "Visualizou a área de Frequência";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega os parâmetros
    $parametroAno = post('parametroAno',get_session('parametroAno',date('Y')));
    $parametroMes = post('parametroMes',get_session('parametroMes',date('m')));
    $parametroLotacao = 66;
        
    # Joga os parâmetros par as sessions    
    set_session('parametroAno',$parametroAno);
    set_session('parametroMes',$parametroMes);
    
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
            
            $menu1->show();
            
        ################################################################
    
            # Formulário de Pesquisa
            $form = new Form('?');

            # Cria um array com os anos possíveis
            $anoInicial = 1999;
            $anoAtual = date('Y');
            $anoExercicio = arrayPreenche($anoInicial,$anoAtual);

            $controle = new Input('parametroAno','combo','Ano:',1);
            $controle->set_size(8);
            $controle->set_title('Filtra por Ano exercício');
            $controle->set_array($anoExercicio);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroAno);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            
            # Mês
            $controle = new Input('parametroMes','combo','Mês:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra pelo Mês');
            $controle->set_array($mes);
            $controle->set_valor($parametroMes);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(7);
            $form->add_item($controle);

            $form->show();
            
        ################################################################
            $grid = new Grid();
            $grid->abreColuna(4);
            
            $painel = new Callout();
            $painel->abre();
            
            $cal = new Calendario($parametroMes,$parametroAno,"p");
            $cal->show();
            
            $painel->fecha();
            
            $grid->fechaColuna();
            
        ################################################################   
            
            $grid->abreColuna(8);
            
            $data = $parametroAno.'-'.$parametroMes.'-01';
            
            # Licença
            $select = '(SELECT tbservidor.idfuncional,
                               tbpessoa.nome,
                               tbperfil.nome,
                               tblicenca.dtInicial,
                               tblicenca.numDias,
                               ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),                               
                               CONCAT(tbtipolicenca.nome,"<br/>",IFNULL(tbtipolicenca.lei,"")),
                              tbservidor.idServidor
                          FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                               JOIN tbhistlot USING (idServidor)
                                               JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                          LEFT JOIN tblicenca USING (idServidor)
                                          LEFT JOIN tbtipolicenca USING (idTpLicenca)
                                          LEFT JOIN tbperfil USING (idPerfil)
                        WHERE tbservidor.situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND (("'.$data.'" BETWEEN tblicenca.dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                           OR  (LAST_DAY("'.$data.'") BETWEEN tblicenca.dtInicial AND ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1))
                           OR  ("'.$data.'" < tblicenca.dtInicial AND LAST_DAY("'.$data.'") > ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1)))';
            # lotacao
            if(!is_null($parametroLotacao)){
                # Verifica se o que veio é numérico
                if(is_numeric($parametroLotacao)){
                    $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
                }else{ # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")'; 
                }
            }
            
            $select .= ') UNION (
                      SELECT tbservidor.idfuncional,
                             tbpessoa.nome,
                             tbperfil.nome,
                             tblicencapremio.dtInicial,
                             tblicencapremio.numDias,
                             ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1),
                             (SELECT CONCAT(tbtipolicenca.nome,"<br/>",IFNULL(tbtipolicenca.lei,"")) FROM tbtipolicenca WHERE idTpLicenca = 6),
                              tbservidor.idServidor
                        FROM tbtipolicenca,tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                                           JOIN tbhistlot USING (idServidor)
                                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                                      LEFT JOIN tblicencapremio USING (idServidor)
                                                      LEFT JOIN tbperfil USING (idPerfil)
                        WHERE tbtipolicenca.idTpLicenca = 6 AND tbservidor.situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND (("'.$data.'" BETWEEN tblicencapremio.dtInicial AND ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1))
                           OR  (LAST_DAY("'.$data.'") BETWEEN tblicencapremio.dtInicial AND ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1))
                           OR  ("'.$data.'" < tblicencapremio.dtInicial AND LAST_DAY("'.$data.'") > ADDDATE(tblicencapremio.dtInicial,tblicencapremio.numDias-1)))';

            # lotacao
            if(!is_null($parametroLotacao)){
                # Verifica se o que veio é numérico
                if(is_numeric($parametroLotacao)){
                    $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
                }else{ # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")';
                }
            }

            $select .= ') UNION (
                       SELECT tbservidor.idfuncional,
                              tbpessoa.nome,
                              tbperfil.nome,
                              tbferias.dtInicial,
                              tbferias.numDias,
                              ADDDATE(tbferias.dtInicial,tbferias.numDias-1),
                              CONCAT("Férias ",tbferias.anoExercicio),
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         LEFT JOIN tbferias USING (idServidor)
                                         LEFT JOIN tbperfil USING (idPerfil)
                        WHERE tbservidor.situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND (("'.$data.'" BETWEEN tbferias.dtInicial AND ADDDATE(tbferias.dtInicial,tbferias.numDias-1))
                           OR  (LAST_DAY("'.$data.'") BETWEEN tbferias.dtInicial AND ADDDATE(tbferias.dtInicial,tbferias.numDias-1))
                           OR  ("'.$data.'" < tbferias.dtInicial AND LAST_DAY("'.$data.'") > ADDDATE(tbferias.dtInicial,tbferias.numDias-1)))';
            # lotacao
            if(!is_null($parametroLotacao)){
                # Verifica se o que veio é numérico
                if(is_numeric($parametroLotacao)){
                    $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
                }else{ # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")'; 
                }
            }

            $select .= ') UNION (
                       SELECT tbservidor.idfuncional,
                              tbpessoa.nome,
                              tbperfil.nome,
                              tbatestado.dtInicio,
                              tbatestado.numDias,
                              ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1),
                              "Falta Abonada",
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         LEFT JOIN tbatestado USING (idServidor)
                                         LEFT JOIN tbperfil USING (idPerfil)
                        WHERE tbservidor.situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND (("'.$data.'" BETWEEN tbatestado.dtInicio AND ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1))
                           OR  (LAST_DAY("'.$data.'") BETWEEN tbatestado.dtInicio AND ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1))
                           OR  ("'.$data.'" < tbatestado.dtInicio AND LAST_DAY("'.$data.'") > ADDDATE(tbatestado.dtInicio,tbatestado.numDias-1)))';
            # lotacao
            if(!is_null($parametroLotacao)){
                # Verifica se o que veio é numérico
                if(is_numeric($parametroLotacao)){
                    $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
                }else{ # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")'; 
                }
            }

            $select .= ') UNION (
                       SELECT tbservidor.idfuncional,
                              tbpessoa.nome,
                              tbperfil.nome,
                              tbtrabalhotre.data,
                              tbtrabalhotre.dias,
                              ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1),
                              "Trabalhando no TRE",
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         LEFT JOIN tbtrabalhotre USING (idServidor)
                                         LEFT JOIN tbperfil USING (idPerfil)
                        WHERE tbservidor.situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND (("'.$data.'" BETWEEN tbtrabalhotre.data AND ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1))
                           OR  (LAST_DAY("'.$data.'") BETWEEN tbtrabalhotre.data AND ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1))
                           OR  ("'.$data.'" < tbtrabalhotre.data AND LAST_DAY("'.$data.'") > ADDDATE(tbtrabalhotre.data,tbtrabalhotre.dias-1)))';
            # lotacao
            if(!is_null($parametroLotacao)){
                # Verifica se o que veio é numérico
                if(is_numeric($parametroLotacao)){
                    $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
                }else{ # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")'; 
                }
            }

            $select .= ') UNION (
                       SELECT tbservidor.idfuncional,
                              tbpessoa.nome,
                              tbperfil.nome,
                              tbfolga.data,
                              tbfolga.dias,
                              ADDDATE(tbfolga.data,tbfolga.dias-1),
                              "Folga TRE",
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                         LEFT JOIN tbfolga USING (idServidor)
                                         LEFT JOIN tbperfil USING (idPerfil)
                        WHERE tbservidor.situacao = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND (("'.$data.'" BETWEEN tbfolga.data AND ADDDATE(tbfolga.data,tbfolga.dias-1))
                           OR  (LAST_DAY("'.$data.'") BETWEEN tbfolga.data AND ADDDATE(tbfolga.data,tbfolga.dias-1))
                           OR  ("'.$data.'" < tbfolga.data AND LAST_DAY("'.$data.'") > ADDDATE(tbfolga.data,tbfolga.dias-1)))';
            # lotacao
            if(!is_null($parametroLotacao)){
                # Verifica se o que veio é numérico
                if(is_numeric($parametroLotacao)){
                    $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
                }else{ # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")'; 
                }
            }

            $select .= ') ORDER BY 2, 4';

            $result = $pessoal->select($select);
            $cont = $pessoal->count($select);
            
            $tabela = new Tabela();   
            $tabela->set_titulo('Servidores com Afastamentos');

            $tabela->set_label(array('IdFuncional','Nome','Perfil','Data Inicial','Dias','Data Final','Descrição'));
            $tabela->set_align(array('center','left','center','center','center','center','left'));
            $tabela->set_funcao(array(NULL,NULL,NULL,"date_to_php",NULL,"date_to_php"));
            
            $tabela->set_rowspan(1);
            $tabela->set_grupoCorColuna(1);
            
            $tabela->set_conteudo($result);
            
            if($cont>0){
                $tabela->show();
            }else{
                titulotable('Servidores com Afastamentos');
                callout("Nenhum valor a ser exibido !","secondary");
            }
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;
        
    ################################################################
                
    }
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}


