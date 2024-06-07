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

    $select = "SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbdocumentacao.cpf,
                      tbpessoa.dtNasc,
                      tbservidor.idServidor,
                      tbperfil.nome,
                      tbservidor.dtAdmissao,
                      tbservidor.dtDemissao,
                      tbservidor.dtPublicAdm,
                      MONTH(tbservidor.dtAdmissao)
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                
                                 LEFT JOIN tbperfil ON(tbservidor.idPerfil = tbperfil.idPerfil)
                                 LEFT JOIN tbdocumentacao ON (tbpessoa.idPessoa = tbdocumentacao.idPessoa)
                WHERE YEAR(tbservidor.dtAdmissao) = '{$relatorioAno}'
                  AND tbperfil.tipo <> 'Outros'    
             ORDER BY MONTH(tbservidor.dtAdmissao), dtadmissao";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Servidores Admitidos em ' . $relatorioAno);
    $relatorio->set_subtitulo('Ordenado pela Data de Admissão');

    $relatorio->set_label(['IdFuncional', 'Nome', 'CPF', 'Nascimento', 'Cargo', 'Perfil', 'Admissão', 'Demissão', 'Publicação', 'Mês']);
    $relatorio->set_align(['center', 'left', 'center', 'center', 'left']);
    $relatorio->set_funcao([null, null, null, "date_to_php", null, null, "date_to_php", "date_to_php", "date_to_php", "get_NomeMes"]);

    $relatorio->set_classe([null, null, null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, null, null, "get_cargo"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(9);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_formCampos(array(
        array('nome' => 'ano',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 4,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'padrao' => $relatorioAno,
            'col' => 3,
            'linha' => 1)));

    $relatorio->set_formFocus('ano');
    
    $relatorio->show();

    $page->terminaPagina();
}
?>
