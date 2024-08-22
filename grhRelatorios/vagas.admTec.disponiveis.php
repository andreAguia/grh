<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

# Pega os parâmetros
$parametroCargo = get('parametroCargo', 6);
$parametroVagas = get('parametroVagas', "Todas");

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $concurso = new Concurso();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######
    # Monta o select
    $select = "SELECT idServidor,
                              idServidor,
                              idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbperfil USING (idPerfil)
                                         LEFT JOIN tbcargo USING (idCargo)
                                         LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                        WHERE (idPerfil = 1 OR idPerfil = 4)                       
                          AND (idServidorOcupanteAnterior is null OR idServidorOcupanteAnterior = 0)
                          AND tbtipocargo.tipo = 'Adm/Tec'
                          AND tbtipocargo.idTipoCargo = {$parametroCargo}
                     ORDER BY dtAdmissao, tbpessoa.nome";

    # Pega os dados
    $row = $pessoal->select($select);

    $tipocargo = new TipoCargo();
    $titulo = $tipocargo->get_cargo($parametroCargo);

    if ($parametroVagas <> "Todas") {
        $titulo .= "<br/>Vagas {$parametroVagas}";

        # altera a tabela para quando for Vagas Ocupadas
        if ($parametroVagas == "Ocupadas") {

            # Percorre o array
            foreach ($row as $item) {

                # Verifica se é ativo 
                if ($pessoal->get_idSituacao($item[0]) == 1) {
                    $arrayOcupadas[] = $item;
                } elseif ($pessoal->get_idSituacao($concurso->get_idOcupantePosterior($item[0])) == 1) {
                    $arrayOcupadas[] = $item;
                } elseif ($pessoal->get_idSituacao($concurso->get_idOcupantePosteriorPosterior($item[0])) == 1) {
                    $arrayOcupadas[] = $item;
                }
            }
        }

        # altera a tabela para quando for Vagas Ocupadas
        if ($parametroVagas == "Disponíveis") {

            # Percorre o array
            foreach ($row as $item) {

                # Verifica se tem alguem ativo 
                if ($pessoal->get_idSituacao($item[0]) == 1) {
                    continue;
                } elseif ($pessoal->get_idSituacao($concurso->get_idOcupantePosterior($item[0])) == 1) {
                    continue;
                } elseif ($pessoal->get_idSituacao($concurso->get_idOcupantePosteriorPosterior($item[0])) == 1) {
                    continue;
                } else {
                    $arrayDisponiveis[] = $item;
                }
            }
        }
    }

    # Relatório
    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Vagas<br/>Administrativos e Técnicos');
    $relatorio->set_subtitulo($titulo);

    if ($parametroVagas == "Ocupadas") {
        $relatorio->set_conteudo($arrayOcupadas);
    }
    if ($parametroVagas == "Disponíveis") {
        $relatorio->set_conteudo($arrayDisponiveis);
    }
    if ($parametroVagas == "Todas") {
        $relatorio->set_conteudo($row);
    }

    $relatorio->set_label(["Primeiro na Vaga", "Vaga Posterior", "Vaga Posterior"]);
    $relatorio->set_width([33, 33, 33]);
    $relatorio->set_align(["left", "left", "left"]);
    $relatorio->set_numeroOrdem(true);
    $relatorio->set_bordaInterna(true);

    $relatorio->set_classe(["Concurso", "Concurso", "Concurso", "Concurso"]);
    $relatorio->set_metodo(["exibeServidorEConcursoRel", "exibeOcupantePosteriorRel", "exibeOcupantePosteriorPosteriorRel"]);
    $relatorio->show();

    $page->terminaPagina();
}