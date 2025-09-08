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

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Pega os parâmetros dos relatórios
    $relatorioDtInicial = post('dtInicial', date('Y') . "-01-01");
    $relatorioDtfinal = post('dtFinal', date('Y') . "-12-31");

    ######

    $select = "SELECT CONCAT(tbservidor.idFuncional, ' - ', tbpessoa.nome),
                      dtInicial,
                      tblicencapremio.numdias,
                      ADDDATE(dtInicial,tblicencapremio.numDias-1),
                      IFNULL(CONCAT(DATE_FORMAT(dtInicioPeriodo, '%d/%m/%Y'),' - ',DATE_FORMAT(dtFimPeriodo, '%d/%m/%Y')),'---'),
                      tbpublicacaopremio.dtPublicacao,
                      idLicencaPremio
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tblicencapremio  USING (idServidor)
                                 LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                                 JOIN tbperfil USING (idPerfil)
                WHERE tbperfil.tipo <> 'Outros'
                  AND situacao = 1
             ORDER BY tbpessoa.nome, dtInicioPeriodo, dtInicial";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Fruição de Licença Prêmio Geral');
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    #$relatorio->set_width([10, 30, 10, 10, 5, 25, 10]);
    $relatorio->set_label(['Servidor', 'Data Inicial', 'Dias', 'Data Final', 'Período', 'Publicação']);
    $relatorio->set_align(["left"]);
    $relatorio->set_funcao([null, "date_to_php", null, "date_to_php", null, "date_to_php"]);
    $relatorio->set_bordaInterna(true);
    $relatorio->set_numGrupo(0);
    $relatorio->set_conteudo($result);
    $relatorio->show();
    $page->terminaPagina();
}