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
                      tbservidor.dtAdmissao
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbperfil USING (idPerfil)  
                                 JOIN tbcargo USING (idCargo)
                                 JOIN tbtipocargo USING (idTipoCargo)
                WHERE year(dtAdmissao) >= "' . $parametroAno . '"
                  AND tbservidor.situacao = 1
                  AND tbtipocargo.tipo = "Professor"
                  AND tbperfil.tipo <> "Outros"
             ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();    
    $relatorio->set_titulo('Relatório de Professores Ativos');
    $relatorio->set_tituloLinha2("Admitidos a Partir de {$parametroAno}");    
    $relatorio->set_subtitulo('Ordenados pelo Nome');
    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Admissão']);
    $relatorio->set_align(["center", "left", "center", "center"]);
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