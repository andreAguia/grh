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

    ###### Relatório 1

    $select = "SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbdocumentacao.cpf,
                      tbpessoa.dtNasc,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbperfil.nome,
                      tbservidor.dtAdmissao,
                      tbservidor.dtDemissao,
                      tbservidor.dtPublicExo,
                      tbmotivo.motivo,
                      MONTH(tbservidor.dtDemissao)
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                
                                 LEFT JOIN tbperfil ON(tbservidor.idPerfil = tbperfil.idPerfil)
                                 LEFT JOIN tbdocumentacao ON (tbpessoa.idPessoa = tbdocumentacao.idPessoa)
                                 LEFT JOIN tbmotivo ON (tbservidor.motivo = tbmotivo.idMotivo)
                WHERE YEAR(tbservidor.dtDemissao) = '{$relatorioAno}'
                  AND tbperfil.tipo <> 'Outros'   
             ORDER BY MONTH(tbservidor.dtDemissao), dtDemissao";


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Anual de Servidores Exonerados, Aposentados e Demitidos em ' . $relatorioAno);
    $relatorio->set_subtitulo('Ordenado pela Data de Demissão');

    $relatorio->set_label(['IdFuncional', 'Nome', 'CPF', 'Nascimento', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Saída', 'Publicação', 'Motivo', 'Mês']);
    $relatorio->set_align(['center', 'left', 'center', 'center', 'left', 'left', 'center', 'center', 'center', 'center', 'left']);
    $relatorio->set_funcao([null, null, null, "date_to_php", null, null, null, "date_to_php", "date_to_php", "date_to_php", null, "get_NomeMes"]);

    $relatorio->set_classe([null, null, null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, null, null, "get_cargo", "get_lotacao"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(11);
    
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