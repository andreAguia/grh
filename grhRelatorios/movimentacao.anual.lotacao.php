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
    $relatorioAno = post('ano', date('Y'));

    ######

    $select = "SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbhistlot.data,
                      tbhistlot.idHistLot,
                      tbhistlot.lotacao,
                      MONTH(tbhistlot.data)
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbhistlot USING (idServidor)
                                 JOIN tbperfil USING (idPerfil)     
               WHERE tbservidor.situacao = 1
                 AND tbperfil.tipo <> 'Outros'  
                 AND YEAR(tbhistlot.data) = {$relatorioAno}  
             ORDER BY tbhistlot.data";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Movimentação de Lotação de Servidores');
    $relatorio->set_subtitulo('Ordenados pela Data da Movimentação');
    $relatorio->set_tituloLinha2($relatorioAno);

    $relatorio->set_label(["IdFuncional", "Nome", "Data", "Saiu de", "Foi para","Mês"]);
    $relatorio->set_align(["center", "left"]);
    $relatorio->set_funcao([null, null, "date_to_php",null,null,"get_nomeMes"]);

    $relatorio->set_classe([null, null, null, "Lotacao", "Lotacao"]);
    $relatorio->set_metodo([null, null, null, "getNomeLotacaoAnterior", "getLotacao"]);
    $relatorio->set_numGrupo(5);

    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'padrao' => $relatorioAno,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}
