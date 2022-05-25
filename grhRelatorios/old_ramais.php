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

    ######  FENORTE

    $select = 'SELECT DIR,
                     CONCAT(GER," - ",nome),
                     ramais
                FROM tblotacao
               WHERE UADM = "FENORTE"
                 AND ativo = "Sim"
                 AND GER <> "CEDIDO"
                 AND ramais <> ""
               ORDER BY DIR,GER';

    $result = $servidor->select($select);

    # Mensagem
    $parametro = '<br> - Os ramais são os quatro últimos dígitos (em parêntesis na tabela);<br/>
    - Para acessar linha externa, nos ramais previamente liberados, tecla 9 + Nº desejado;<br/>
    - Para transferência de ligação, tecla FLASH + Nº do ramal a ser direcionada a chamada;<br/>
    - Para utilizar cadeado eletrônico basta digitar 71 + CODIGO DE BLOQUEIO;<br/>
    - Para desativar cadeado eletrônico basta digitar 701 + CODIGO DE BLOQUEIO;<br/>
    - Para Fax será utilizado o Nº(22) 2738-0868. Sendo necessário originar chamada para o mesmo;<br/>
    - Utilizar sempre a operadora 41.<br><br>';

    $relatorio = new Relatorio('relatorioRamal');
    $relatorio->set_titulo('FENORTE');
    $relatorio->set_subtitulo('Telefones e Ramais');
    $relatorio->set_label(array('Diretoria', 'Setor', 'Ramais'));
    $relatorio->set_width(array(0, 40, 30));
    $relatorio->set_align(array("center", "left"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(0);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_subTotal(false);
    $relatorio->set_funcaoAntesTitulo('p');
    $relatorio->set_funcaoAntesTituloParametro($parametro);
    $relatorio->show();

    ######  TECNORTE

    echo '<br/>';

    $servidor = new Pessoal();
    $select = 'SELECT DIR,
                     CONCAT(GER," - ",nome),
                     ramais
                FROM tblotacao
               WHERE UADM = "TECNORTE" 
                 AND ativo = "Sim"
                 AND GER <> "CEDIDO"
                 AND ramais <> ""
               ORDER BY DIR,GER';

    $result = $servidor->select($select);

    $relatorio = new Relatorio('relatorioRamal');
    $relatorio->set_titulo('TECNORTE');
    $relatorio->set_subtitulo('Telefones e Ramais');
    $relatorio->set_label(array('Diretoria', 'Setor', 'Ramais'));
    $relatorio->set_width(array(0, 40, 30));
    $relatorio->set_align(array("center", "left"));
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(0);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_log(false);
    $relatorio->show();

    $page->terminaPagina();
}