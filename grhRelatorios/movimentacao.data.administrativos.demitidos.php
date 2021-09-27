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
    $relatorioData = post('data', date('Y-m-d'));
    
    ######

    $select = 'SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbperfil.nome,
                      tbservidor.dtAdmissao,
                      tbservidor.dtDemissao,
                      tbmotivo.motivo
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                
                                    LEFT JOIN tbperfil ON(tbservidor.idPerfil = tbperfil.idPerfil)
                                    LEFT JOIN tbmotivo ON (tbservidor.motivo = tbmotivo.idMotivo)
                 WHERE tbservidor.dtDemissao > "' . $relatorioData . '"
                  AND (tbservidor.idCargo <>128 AND tbservidor.idCargo <> 129)
                  AND tbservidor.idPerfil = 1
             ORDER BY dtDemissao';


    $result = $servidor->select($select);
    
    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Estatutários Administrativos & Técnicos Demitidos e Exonerados a Partir ' . date_to_php($relatorioData));
    $relatorio->set_subtitulo('Ordenado pela Data de Saída');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Perfil', 'Admissão', 'Saída', 'Motivo'));
    #$relatorio->set_width(array(10,20,10,10,10,10,10,10,10));
    $relatorio->set_align(array('center', 'left', 'left', 'left','center','center','center','left'));
    $relatorio->set_funcao(array(null, null, null, null, null, "date_to_php", "date_to_php"));

    $relatorio->set_classe(array(null, null, "pessoal", "pessoal"));
    $relatorio->set_metodo(array(null, null, "get_cargoSimples", "get_lotacao"));

    $relatorio->set_conteudo($result);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_formCampos(array(
        array('nome' => 'data',
            'label' => 'A partir de:',
            'tipo' => 'data',
            'size' => 4,
            'title' => 'A partir desta data',
            'padrao' => $relatorioData,
            'col' => 3,
            'linha' => 1),
        array('nome' => 'submit',
            'linha' => 1,
            'size' => 10,
            'valor' => 'Pesquisar',
            'label' => null,
            'tipo' => 'submit')
        ));

    $relatorio->set_formFocus('ano');
    $relatorio->set_formLink('?');
    $relatorio->show();

    $page->terminaPagina();
}
?>
