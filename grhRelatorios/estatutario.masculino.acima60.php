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
$acesso = Verifica::acesso($idUsuario, 2);

# Pega a lotação
$parametroLotacao = get_session('parametroLotacao');

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    if ($parametroLotacao == "*") {
        $parametroLotacao = null;
    }
    
    $subTitulo = null;

    ######
    # Exibe a lista
    $select = "SELECT idFuncional,
                              tbpessoa.nome,
                              idServidor,
                              idServidor,
                              TIMESTAMPDIFF(YEAR, dtNasc, NOW()) AS idade,
                              idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING(idPessoa)
                                              JOIN tbhistlot USING (idServidor)
                                              JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE tbservidor.situacao = 1
                          AND idPerfil = 1
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                          AND tbpessoa.sexo = 'masculino'
                          AND TIMESTAMPDIFF(YEAR, dtNasc, NOW()) >= 60";

    if (!is_null($parametroLotacao)) {  // senão verifica o da classe
        if (is_numeric($parametroLotacao)) {
            $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
            $subTitulo = $servidor->get_nomeLotacao2($parametroLotacao);
        } else { # senão é uma diretoria genérica
            $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
            $subTitulo = $parametroLotacao;
        }
    }

    $select .= " ORDER BY idade";
    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Servidores Estatutários do Sexo Masculino com 60 anos ou Mais');
    $relatorio->set_tituloLinha2($subTitulo);
    $relatorio->set_subtitulo('Ordenados pela Idade');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Idade']);
    $relatorio->set_align(["center", "left", "left", "left"]);

    $relatorio->set_classe([null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, "get_Cargo", "get_lotacao"]);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}