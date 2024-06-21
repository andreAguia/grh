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
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {

    # Verifica a fase do programa
    $fase = get('fase');

    # Pega os parâmetros
    $parametroLotacao = post('parametroLotacao', get_session('parametroLotacao', $pessoal->get_idLotacao($intra->get_idServidor($idUsuario))));

    # Joga os parâmetros par as sessions
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
            $controle->set_linha(1);
            $controle->set_col(12);
            $form->add_item($controle);

            # submit
            $controle = new Input('submit', 'submit');
            $controle->set_valor('Imprimir');
            $controle->set_linha(2);
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
                                              JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idperfil)
                        WHERE tbservidor.situacao = 1
                          AND tbperfil.tipo <> "Outros" 
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

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
            $contador = 0;

            # Imprime cada servidor retornado
            foreach ($result as $item) {

                if ($contador == 0) {
                    $form = new FormularioSaude($item[0], $idUsuario, true);
                } else {
                    $form = new FormularioSaude($item[0], $idUsuario, false);
                }
                $contador++;
            }
            break;
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}    