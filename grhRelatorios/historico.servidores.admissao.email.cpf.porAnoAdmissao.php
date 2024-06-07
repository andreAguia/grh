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

    # Pega o parâmetro do relatório
    $cargo = post('cargo', "Adm/Tec");

    # Pega o parâmetro do ano
    $parametroAno = post('parametroAno', date('Y'));

    ######

    $select = "SELECT tbservidor.idfuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbdocumentacao.cpf,
                     tbpessoa.emailUenf,
                     tbpessoa.emailPessoal,
                     tbpessoa.emailOutro,
                     dtAdmissao,
                     dtDemissao
                FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                JOIN tbdocumentacao USING (idPessoa)
                                JOIN tbcargo USING (idCargo)
                                JOIN tbtipocargo USING (idTipoCargo)
                                JOIN tbperfil USING (idPerfil)   
               WHERE year(dtAdmissao) = '{$parametroAno}'
                 AND tbtipocargo.tipo = '{$cargo}'
                 AND tbperfil.tipo <> 'Outros'
            ORDER BY tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Servidores');
    $relatorio->set_tituloLinha2("{$cargo}<br/>Admitidos em {$parametroAno}");
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    
    $relatorio->set_label(['IdFuncional', 'Nome', 'Lotação', 'CPF', 'E-mail UENF', 'E-mail Pessoal', 'Outro E-mail', 'Admissão', 'Saída']);
    $relatorio->set_align(["center", "left", "left", "center", "left", "left", "left"]);
    $relatorio->set_funcao([null, null, null, null, null, null, null, "date_to_php", "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_Lotacao"]);

    $relatorio->set_conteudo($result);

    # Seleciona o tipo de cargo
    $listaCargo = $servidor->select('SELECT distinct tipo,tipo from tbtipocargo');

    $relatorio->set_formCampos(array(
        array('nome' => 'cargo',
            'label' => 'Tipo de Cargo:',
            'tipo' => 'combo',
            'array' => $listaCargo,
            'size' => 20,
            'col' => 3,
            'padrao' => $cargo,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
        array('nome' => 'parametroAno',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 10,
            'padrao' => $parametroAno,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));

    $relatorio->set_formFocus('cargo');
    
    $relatorio->show();

    $page->terminaPagina();
}