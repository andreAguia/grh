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

    # Pega o parâmetro do ano
    $parametroAno = post('parametroAno', date('Y'));

    ######

    $select = 'SELECT tbservidor.idFuncional,
                      tbpessoa.nome,
                      tbservidor.idservidor,
                      tbservidor.dtAdmissao,
                      tbpessoa.emailUenf
                 FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                 JOIN tbcargo USING (idCargo)
                                 JOIN tbtipocargo USING (idTipoCargo)
                WHERE year(dtAdmissao) >= "' . $parametroAno . '"
                  AND tbservidor.situacao = 1
                  AND tbtipocargo.tipo = "Professor"
                  AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
             ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Ativos');
    $relatorio->set_subtitulo("Admitidos a Partir de {$parametroAno}<br/>Ordenados pelo Nome do Servidor");
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Admissão', 'Email Uenf']);
    #$relatorio->set_width(array(10,30,10,30,20));
    $relatorio->set_align(["center", "left", "center", "center", "left"]);
    $relatorio->set_classe([null, null, "pessoal"]);
    $relatorio->set_metodo([null, null, "get_cargoSimples"]);
    $relatorio->set_funcao([null, null, null, "date_to_php"]);

    $relatorio->set_conteudo($result);

    $relatorio->set_formCampos(array(
        array('nome' => 'parametroAno',
            'label' => 'Ano:',
            'tipo' => 'texto',
            'size' => 10,
            'padrao' => $parametroAno,
            'title' => 'Ano',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1)));

    $relatorio->set_formFocus('parametroAno');
    $relatorio->set_formLink('?');

    $relatorio->show();

    $page->terminaPagina();
}