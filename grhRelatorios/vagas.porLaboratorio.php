<?php

/**
 * Relatório
 *    
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, 2);

# Pega os parâmetros
$parametroCentro = get_session('parametroCentro');

if ($acesso) {

    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######   
    # Título & Subtitulo
    $subTitulo = $parametroCentro;
    $titulo = "Vagas por Laboratório";

    # Pega os dados
    $select = 'SELECT idVaga,
                      tbcargo.nome,
                      idVaga,
                      idVaga,
                      idVaga,
                      idVaga,
                      (SELECT idLotacao 
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso) 
                        WHERE idVaga = con1.idVaga 
                     ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1)
                 FROM tbvaga con1 LEFT JOIN tbcargo USING (idCargo)
                WHERE true ';

    # parametroCentro
    if (!vazio($parametroCentro)) {
        $select .= "AND centro = '$parametroCentro'";
    }

    $select .= ' ORDER BY centro, 7, idCargo desc';

    $resumo = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($resumo);

    $relatorio->set_label(array("Vaga", "Cargo", "Status", "Último Ocupante", "Obs", "Num. de Concursos", "Origem"));
    $relatorio->set_width(array(5, 20, 10, 30, 25));
    $relatorio->set_align(array("center"));

    #$relatorio->set_funcao(array(null,null,null,null,"date_to_php"));
    #$relatorio->set_width(array(5,5,5,20,5,20,15,15,5));

    $relatorio->set_classe(array(null, null, "Vaga", "Vaga", "Vaga", "Vaga", "Pessoal"));
    $relatorio->set_metodo(array(null, null, "get_status", "get_servidorOcupante", "get_obsOcupante", "get_numConcursoVaga", "get_nomelotacao2"));

    $relatorio->set_titulo($titulo);
    $relatorio->set_subtitulo($subTitulo);

    #$relatorio->set_numeroOrdem(true);
    #$relatorio->set_numeroOrdemTipo('d');
    $relatorio->set_bordaInterna(true);
    $relatorio->set_numGrupo(6);

    $relatorio->show();

    $page->terminaPagina();
}