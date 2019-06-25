<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Servidor logado 
$idUsuario = NULL;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega os parâmetros dos relatórios
    $lotacao = get_session('parametroLotacao');
    $ci = post('ci');
    $chefia = post('chefia');
    
    if($lotacao == "*"){
        $lotacao = NULL;
    }
    
    $grid = new Grid();
    $grid->abreColuna(1);
    $grid->fechaColuna();
    $grid->abreColuna(10);
    
    function exibeTextoSispatri($parametro){
        
        # Pega os parametros
        $lotacao = $parametro[0];
        $ci = $parametro[1];
        $chefia = $parametro[2];
        
        if(!is_null($lotacao)){
            
            $servidor = new Pessoal();
            
            $grid = new Grid();
            $grid->abreColuna(5);
                p("CI GRH/DGA/UENF n° $ci/19","left");
            $grid->fechaColuna();
            $grid->abreColuna(7);
                p("Campos dos Goytacazes, ".dataExtenso(date('d/m/Y')),"right");
            $grid->fechaColuna();
            $grid->fechaGrid();

            $gerenteGrh = $servidor->get_Nome($servidor->get_gerente(66));
            $chefiaImediata = $servidor->get_nome($servidor->get_chefiaImediataIdLotacao($lotacao));
            
            if(vazio($chefia)){
                $chefia = $chefiaImediata;
            }
            
            #$nomeLotacao = $servidor->get_chefiaImediataDescricaoIdLotacao($lotacao);
            $nomeLotacao = $servidor->get_nomeLotacao2($lotacao);
            p("<b>De: $gerenteGrh<br/>Gerente de Recursos Humanos - GRH/UENF</b>","left");
            
            p("Para: $chefia<br/>$nomeLotacao","left");
            
            p("Prezado(a) Senhor(a)","left");
            
            
            $texto = "Conforme resolução conjunta CGE/SEFAZ nº 01 de 15 de agosto de 2018, em cumprimento ao"
                    . " disposto no art. 9º do Decreto nº 46.634, de 17 de julho de 2018, e tendo em vista o"
                    . " que consta no Processo Administrativo nº E-32/001/100001/2018 foi implantado o SISPATRI"
                    . " no âmbito do Poder Executivo Estadual. O agente público deve acessar www.servidor.rj.gov.br"
                    . " e fazer a declaração de bens e valores (DBV) on-line. Informamos abaixo em epígrafe relação"
                    . " dos agentes públicos da sua unidade administrativa que não entregaram a declaração até a presente data."
                    . " <b>Salientamos que o prazo encerra em 30 de junho de 2019.</b> Conforme art.6º §2º a não apresentação por"
                    . " parte do agente público acarretará a abertura de Processo Administrativo Disciplinar.";
            
            p($texto,"justify");
        }
    }
    
    #####
    
    function exibeTextoFinal(){       
        
        $grid = new Grid();
        $grid->abreColuna(3);
            br();
            p("Atenciosamente,","left");
        $grid->fechaColuna();
        $grid->abreColuna(6);
        
            $figura = new Imagem(PASTA_FIGURAS.'assinatura.png','Assinatura do Gerente',120,140);
            $figura->show();
            
            $servidor = new Pessoal();
            $gerenteGrh = $servidor->get_Nome($servidor->get_gerente(66));
            $idGerente = $servidor->get_idFuncional($servidor->get_gerente(66));
            p("$gerenteGrh<br/>Gerente de Recursos Humanos<br/>Id Funcional: $idGerente","center","f12");
        
        $grid->fechaColuna();
        $grid->abreColuna(3);
        $grid->fechaColuna();
        $grid->fechaGrid();        
        
        p("_______________________________________________________________________________<br/>Av. Alberto Lamego 2000 - Parque California - Campos dos Goytacazes/RJ - 28013-602<br/>Tel.: (22) 2739-7064 - correio eletronico: grh@uenf.br","center","f12");
        
    }
        
    ######
    
    $select ='SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     concat(IFNULL(tblotacao.UADM,"")," - ",IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                FROM tbsispatri JOIN tbservidor USING (idServidor)
                                JOIN tbpessoa USING (idPessoa)
                                JOIN tbdocumentacao USING (idPessoa)
                                JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';
    
    # lotacao
    if(!is_null($lotacao)){
        # Verifica se o que veio é numérico
        if(is_numeric($lotacao)){
            $select .= ' AND (tblotacao.idlotacao = "'.$lotacao.'")'; 
        }else{ # senão é uma diretoria genérica
            $select .= ' AND (tblotacao.DIR = "'.$lotacao.'")';
        }
    }
    
    $select .= ' ORDER BY tblotacao.UADM,tblotacao.DIR,tblotacao.GER,tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $parametro = array($lotacao,$ci,$chefia);
    $relatorio->set_funcaoAntesTitulo('exibeTextoSispatri');
    $relatorio->set_funcaoAntesTituloParametro($parametro);
    
    $relatorio->set_funcaoFinalRelatorio('exibeTextoFinal');
    
    #$relatorio->set_titulo('Relatório de Servidores Que nao Entregaram o Sispatri');
    
    $relatorio->set_label(array('idFuncional','Nome','Lotaçao'));
    $relatorio->set_width(array(20,80));
    $relatorio->set_align(array("center","left"));
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_linhaNomeColuna(FALSE);
    
    $relatorio->set_numGrupo(2);
    $relatorio->set_conteudo($result);
    
    $result = $servidor->select('(SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');
    array_unshift($result,array('*','-- Todos --'));
    
    $chefiaImediata = $servidor->get_nome($servidor->get_chefiaImediataIdLotacao($lotacao));
    if(vazio($chefia)){
        $chefia = $chefiaImediata;
    }

    $relatorio->set_formCampos(array(
                               array ('nome' => 'chefia',
                                      'label' => 'Chefia Imediata:',
                                      'tipo' => 'texto',
                                      'size' => 200,
                                      'col' => 9,
                                      'padrao' => $chefia,
                                      'onChange' => 'formPadrao.submit();',
                                      'linha' => 1),
                               array ('nome' => 'ci',
                                      'label' => 'N° CI:',
                                      'tipo' => 'texto',
                                      'size' => 4,
                                      'col' => 3,
                                      'padrao' => $ci,
                                      'onChange' => 'formPadrao.submit();',
                                      'linha' => 1)
        ));

    $relatorio->set_formFocus('lotacao');
    $relatorio->set_formLink('?');
    $relatorio->show();
    
    $grid->fechaColuna();
    $grid->abreColuna(1);
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}