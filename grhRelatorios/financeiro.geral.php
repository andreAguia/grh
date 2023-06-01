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
                 AND tbperfil.tipo <> "Outros" 
            ORDER BY 9,2';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral Financeiro');
    $relatorio->set_subtitulo('Agrupado por Perfil - Ordenados por Matricula');

    $relatorio->set_label(['IdFuncional', 'Nome', 'Salário', 'Triênio', 'Comissão', 'Gratificação Especial', 'Salário Cedidos', 'Total', 'Perfil']);
    $relatorio->set_width([10, 30, 10, 10, 10, 10, 10, 10, 0]);
    $relatorio->set_align(["center", "left", "right", "right", "right", "right", "right", "right"]);
    $relatorio->set_funcao([null, null, "formataMoeda", "formataMoeda", "formataMoeda", "formataMoeda", "formataMoeda", "formataMoeda"]);
    $relatorio->set_classe(["", "", "pessoal", "Trienio", "pessoal", "pessoal", "pessoal", "pessoal"]);
    $relatorio->set_metodo(["", "", "get_salarioBase", "getValor", "get_salarioCargoComissao", "get_gratificacao", "get_salarioCessao", "get_salarioTotal"]);
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(8);
    #$relatorio->set_botaoVoltar('../sistema/areaServidor.php');
    $relatorio->show();

    $page->terminaPagina();
}
