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

# Parámetro da Lei Complementar
$dataLei = date_to_bd("05/10/2021");

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = "SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.dtAdmissao,
                      tbservidor.idServidor
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                      JOIN tbconcurso USING (idConcurso)
                WHERE idPerfil = 1
                  AND situacao = 1
                  AND tbconcurso.dtPublicacaoEdital >= '{$dataLei}'
                  AND tbservidor.dtAdmissao <= DATE_SUB(NOW(), INTERVAL 3 YEAR);    
            ORDER BY tbservidor.dtAdmissao";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo("Relatório de Servidores Estatutários Ativos");
    $relatorio->set_subtitulo("Afetados Pela Lei Complementar 194/2021</br>Com Mais de 3 Anos de Admissão<br/>Ordenado Pela Data de Admissão");
    $relatorio->set_label(['IdFuncional', 'Servidor', 'Cargo', 'Admissão', 'Edital Concurso']);
    $relatorio->set_align(["center", "left"]);
    $relatorio->set_width([10, 25, 20, 10, 15]);
    $relatorio->set_funcao([null, null, null, "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal", null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_cargoSimples", null, "get_concursoDataEdital"]);

    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(3);
    $relatorio->set_totalRegistro(false);
    $relatorio->show();

    $page->terminaPagina();
}