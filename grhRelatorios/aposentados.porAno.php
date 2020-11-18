<?php

/**
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

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $parametroAno = get_session('parametroAno', date('Y'));

    ######

    $select = 'SELECT tbservidor.idfuncional,
                      tbservidor.idServidor,
                      tbservidor.dtAdmissao,
                      tbservidor.dtDemissao,
                      tbmotivo.motivo
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                WHERE YEAR(tbservidor.dtDemissao) = "' . $parametroAno . '"
                  AND situacao = 2
                  AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
             ORDER BY dtDemissao';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Servidores Aposentados em ' . $parametroAno);
    #$relatorio->set_tituloLinha2('Com Informaçao de Contatos');
    $relatorio->set_subtitulo('Ordenado pela Data de Saída');

    $relatorio->set_label(array('IdFuncional', 'Servidor', 'Admissão', 'Saída', 'Motivo'));
    $relatorio->set_align(array('center', 'left', 'center', 'center', 'left'));
    $relatorio->set_funcao(array(null, null, "date_to_php", "date_to_php"));

    $relatorio->set_classe(array(null, "pessoal"));
    $relatorio->set_metodo(array(null, "get_nomeECargo"));

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}
?>
