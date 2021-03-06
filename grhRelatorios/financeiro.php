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
$acesso = Verifica::acesso($idUsuario, 2);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = 'SELECT tbservidor.idfuncional,
                     tbpessoa.nome,                 
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbperfil.nome
                FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                   LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idperfil)
               WHERE tbservidor.situacao = 1
                 AND tbservidor.idPerfil <> 10
            ORDER BY 9,2';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral Financeiro');
    $relatorio->set_subtitulo('Agrupado por Perfil - Ordenados por Matricula');

    $relatorio->set_label(array('IdFuncional', 'Nome', 'Salário', 'Triênio', 'Comissão', 'Gratificação Especial', 'Salário Cedidos', 'Total', 'Perfil'));
    $relatorio->set_width(array(10, 30, 10, 10, 10, 10, 10, 10, 0));
    $relatorio->set_align(array("center", "left", "right", "right", "right", "right", "right", "right"));
    $relatorio->set_funcao(array(null, null, "formataMoeda", "formataMoeda", "formataMoeda", "formataMoeda", "formataMoeda", "formataMoeda"));

    $classe = array("", "", "pessoal", "pessoal", "pessoal", "pessoal", "pessoal", "pessoal");
    $metodo = array("", "", "get_salarioBase", "get_trienioValor", "get_salarioCargoComissao", "get_gratificacao", "get_salarioCessao", "get_salarioTotal");

    $relatorio->set_classe($classe);
    $relatorio->set_metodo($metodo);
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(8);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
