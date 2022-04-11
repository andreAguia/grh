<?php

/**
 * Sistema GRH
 * 
 * Folha de Presença
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Conecta ao Banco de Dados    
$pessoal = new Pessoal();

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario);

if ($acesso) {

    # Verifica a fase do programa
    $fase = get('fase');

    # Pega os parâmetros
    $parametroAno = post('parametroAno', get_session('parametroAno', date("Y")));
    $parametroTrimestre = post('parametroTrimestre', get_session('parametroTrimestre', 1));
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));

    # Joga os parâmetros par as sessions
    set_session('parametroAno', $parametroAno);
    set_session('parametroTrimestre', $parametroTrimestre);
    set_session('parametroLotacao', $parametroLotacao);

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    ###

    switch ($fase) {
        case "":
            # Título
            titulo("Folha de Frequência por Lotação");
            
            # Formulário de Pesquisa
            $form = new Form('?fase=aguarde');

            # Cria um array com os anos possíveis
            $anoInicial = 1999;
            $anoAtual = date('Y');
            $ano = arrayPreenche($anoInicial, $anoAtual + 2, "d");

            $controle = new Input('parametroAno', 'combo', 'Ano:', 1);
            $controle->set_size(8);
            $controle->set_title('Informa o Ano');
            $controle->set_array($ano);
            $controle->set_valor(date("Y"));
            $controle->set_valor($parametroAno);
            $controle->set_linha(1);
            $controle->set_col(3);
            $controle->set_autofocus(true);
            $form->add_item($controle);
            
            $controle = new Input('parametroTrimestre', 'combo', 'Trimestre:', 1);
            $controle->set_size(3);
            $controle->set_title('Informa o trimestre');
            $controle->set_array([[1, 'Primeiro'],[2, 'Segundo'],[3, 'Terceiro'],[4, 'Quarto']]);
            $controle->set_valor($parametroTrimestre);
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFnull(tblotacao.DIR,"")," - ",IFnull(tblotacao.GER,"")," - ",IFnull(tblotacao.nome,"")) lotacao
                                              FROM tblotacao
                                             WHERE ativo) UNION (SELECT distinct DIR, DIR
                                              FROM tblotacao
                                             WHERE ativo)
                                          ORDER BY 2');

            $controle = new Input('parametroLotacao', 'combo', 'Lotação:', 1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_linha(2);
            $controle->set_col(12);
            $form->add_item($controle);
            
            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_valor('Imprimir');
            $controle->set_linha(3);
            $form->add_item($controle);
            
            $form->show();
            break;
        
        #######
        
        case "aguarde":
            br(4);
            aguarde();
            br();

            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
            p("Aguarde...", "center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=folha');
            break;
        
        #######
        
        case "folha" :

            ######
            # Corpo do relatorio        
            $select = 'SELECT tbservidor.idServidor
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                      JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                      JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)						   	    
               WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                 AND situacao = 1';

            # lotacao
            if (!is_null($parametroLotacao)) {
                # Verifica se o que veio é numérico
                if (is_numeric($parametroLotacao)) {
                    $select .= ' AND (tblotacao.idlotacao =  "' . $parametroLotacao . '")';
                } else { # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "' . $parametroLotacao . '")';
                }
            }

            $select .= ' ORDER BY lotacao, tbpessoa.nome';
            $result = $pessoal->select($select);

            $folha = new FolhaFrequencia();
            $contador = 0;

            # Imprime cada servidor retornado
            foreach ($result as $item) {
                if ($contador == 0) {
                    $folha->exibeFolha($item[0], $parametroAno, $parametroTrimestre, $idUsuario, true);
                } else {
                    $folha->exibeFolha($item[0], $parametroAno, $parametroTrimestre, $idUsuario, false);
                }
                $contador++;
            }
            break;
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}    