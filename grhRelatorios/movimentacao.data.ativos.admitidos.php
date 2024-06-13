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
    $relatorioData = post('data', date('Y-m-d'));

    ######

    $select = "SELECT tbservidor.idfuncional,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbperfil.nome,
                      tbservidor.dtAdmissao
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)                                
                                 LEFT JOIN tbperfil ON(tbservidor.idPerfil = tbperfil.idPerfil)
                                 LEFT JOIN tbmotivo ON (tbservidor.motivo = tbmotivo.idMotivo)
                 WHERE tbservidor.dtAdmissao >= '{$relatorioData}'
                   AND tbperfil.tipo <> 'Outros'  
                   AND situacao = 1
              ORDER BY dtAdmissao";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Ativos Admitidos');
    $relatorio->set_tituloLinha2('a Partir de ' . date_to_php($relatorioData));
    $relatorio->set_subtitulo('Ordenado pela Data de Admissão');

    $relatorio->set_label(['IdFuncional', 'Nome', 'Cargo', 'Lotação', 'Perfil', 'Admissão']);
    $relatorio->set_width([10, 30, 20, 30, 10]);
    $relatorio->set_align(['center', 'left', 'left', 'left', 'center', 'center']);
    $relatorio->set_funcao([null, null, null, null, null, "date_to_php"]);

    $relatorio->set_classe([null, null, "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, "get_cargoSimples", "get_lotacao"]);

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

    $relatorio->show();
    $page->terminaPagina();
}