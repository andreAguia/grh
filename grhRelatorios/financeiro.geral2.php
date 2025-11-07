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

    $select = "SELECT idfuncional,
                      tbpessoa.nome,
                      tbservidor.dtAdmissao,
                      tbservidor.idServidor,
                      tbservidor.idServidor
                 FROM tbservidor JOIN tbpessoa USING  (idPessoa)
                 WHERE tbservidor.situacao = 1
                   AND idPerfil = 1
              ORDER BY tbpessoa.nome";

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Servidores Estatutarios Ativos');
    $relatorio->set_subtitulo("Com Faixa e Nivel do Plano de Cargos");
    $relatorio->set_label(['IdFuncional', 'Nome', 'Admissão', 'Cargo', 'Nivel Faixa Padrao']);
    #$relatorio->set_width([10, 90]);
    $relatorio->set_align(["center", "left", "left", "left"]);
    $relatorio->set_classe([null, null, null, "Pessoal", "Progressao"]);
    $relatorio->set_metodo([null, null, null, "get_cargoSimples", "get_FaixaAtual"]);
    $relatorio->set_funcao([null, null, "date_to_php"]);
    #$relatorio->set_numGrupo(4);
    $relatorio->set_conteudo($result);
    $relatorio->show();
    $page->terminaPagina();
}