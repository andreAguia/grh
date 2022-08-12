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
    # Monta o select
    $select = "SELECT idfuncional,
                      tbpessoa.nome,
                      idServidor,
                      idServidor,
                      tbservidor.dtDemissao,
                      tbmotivo.motivo
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                WHERE (tbservidor.dtDemissao >= '{$relatorioDtInicial}' AND tbservidor.dtDemissao <= '{$relatorioDtfinal}')
                  AND situacao = 2
                  AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
             ORDER BY dtDemissao";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo("Servidores Aposentados");
    $relatorio->set_tituloLinha2("Período de " . date_to_php($relatorioDtInicial) . " a " . date_to_php($relatorioDtfinal));
    $relatorio->set_subtitulo('Ordenado pela Data de Saída');

    $relatorio->set_label(['IdFuncional', 'Servidor', 'Telefones', 'E-mail', 'Aposentado em']);
    $relatorio->set_align(['center', 'left', 'center', 'center', 'center', 'left']);
    $relatorio->set_funcao([null, null, null, null, "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, "get_telefones", "get_emails"]);
    $relatorio->set_bordaInterna(true);

    $relatorio->set_conteudo($result);

    $relatorio->set_formCampos(array(
        array('nome' => 'dtInicial',
            'label' => 'Início:',
            'tipo' => 'data',
            'size' => 4,
            'title' => 'Insira a data inicial',
            'col' => 3,
            'padrao' => $relatorioDtInicial,
            'linha' => 1),
        array('nome' => 'dtFinal',
            'label' => 'Término:',
            'tipo' => 'data',
            'size' => 4,
            'title' => 'Insira a data final',
            'col' => 3,
            'padrao' => $relatorioDtfinal,
            'linha' => 1),
        array('nome' => 'submit',
            'valor' => 'Atualiza',
            'label' => '-',
            'size' => 4,
            'col' => 3,
            'tipo' => 'submit',
            'title' => 'Atualiza a tabela',
            'linha' => 1),
    ));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}