<?php

/**
 * Sistema GRH
 * 
 * Relatório de Triênio
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
                      tbservidor.idServidor,
                      tbservidor.idServidor					
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)								   	    
                WHERE tbservidor.situacao = 1
                  AND idperfil = 1
             ORDER BY nome';


    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório Geral de Triênio');
    $relatorio->set_tituloLinha2('dos Servidores Estatutários Ativos');
    $relatorio->set_subtitulo('Ordenado por Nome');

    $relatorio->set_label(['Id Funcional', 'Nome', 'Salário', 'Triênio', '%', 'a Partir de', 'Período Aquisitivo', 'Próximo Triênio', 'Processo', 'Publicação']);
    $relatorio->set_align(["center", "left", "right", "right"]);
    $relatorio->set_funcao([null, null, 'formataMoeda', 'formataMoeda']);

    $relatorio->set_classe([null, null, "pessoal", "Trienio", "Trienio", "Trienio", "Trienio", "Trienio", "Trienio", "Trienio"]);
    $relatorio->set_metodo([null, null, "get_salarioBase", "getValor", "exibePercentual", "getDataInicial", "getPeriodoAquisitivo", "getProximoTrienio", "getNumProcesso", "getPublicacao"]);

    $relatorio->set_conteudo($result);
    $relatorio->set_botaoVoltar(null);
    $relatorio->show();

    $page->terminaPagina();
}