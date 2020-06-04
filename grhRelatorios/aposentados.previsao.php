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
    $parametroSexo = get_session('parametroSexo', "Feminino");
    $parametroNome = get_session('parametroNome');

    ######
    # Monta o select
    $select = 'SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
               WHERE tbservidor.situacao = 1
                 AND idPerfil = 1
                 AND tbpessoa.sexo = "' . $parametroSexo . '"';

    if (!is_null($parametroNome)) {
        $select .= ' AND tbpessoa.nome LIKE "%' . $parametroNome . '%"';
    }

    $select .= ' ORDER BY tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Estatutários Ativos com Previsão para Aposentadoria - Sexo: ' . $parametroSexo);

    if (!is_null($parametroNome)) {
        $relatorio->set_subtitulo('Filtro nome: ' . $parametroNome);
    }

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Integral', 'Proporcional', 'Compulsória'));
    #$tabela->set_width(array(30,15,15,15,15));
    $relatorio->set_align(array("center", "left", "left"));
    $relatorio->set_funcaoDepoisClasse(array(null, null, null, "marcaSePassou", "marcaSePassou", "marcaSePassou"));

    $relatorio->set_classe(array(null, null, "pessoal", "Aposentadoria", "Aposentadoria", "Aposentadoria"));
    $relatorio->set_metodo(array(null, null, "get_CargoSimples", "get_dataAposentadoriaIntegral", "get_dataAposentadoriaProporcional", "get_dataAposentadoriaCompulsoria"));

    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}