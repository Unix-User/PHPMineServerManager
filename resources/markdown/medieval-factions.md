## Facções Medievais

Bem-vindo ao mundo das Facções Medievais, um ambiente dinâmico que simula a formação e gestão de nações soberanas. Aqui, você tem a oportunidade de estabelecer sua própria nação, reivindicar terras, envolver-se em conflitos e política, criar leis e organizar torneios de duelo. Em suma, você tem a liberdade de moldar a sociedade à sua vontade. O poder está em suas mãos - você irá ascender ao topo ou desaparecer na obscuridade? O destino da sua nação está em suas mãos.

### Criação da Nação

Inicie sua jornada com o comando `/mf create` para estabelecer sua nova facção.

### Recrutamento da Nação

Lembre-se, a força reside nos números.

Para recrutar outros jogadores, você pode convidá-los usando: `/mf invite [nome-do-jogador]`. Use `/mf join [nome-da-facção]` para solicitar ao líder de uma facção um pedido de aprovação ou para aceitar um convite.

### Gerenciamento da Nação

Para renomear sua facção, use o comando: `/mf rename [novo-nome]`.

Para definir a descrição da sua facção, use o comando: `/mf desc [descrição]`.

Para promover um jogador a oficial da sua facção, use o comando: `/mf promote [nome-do-jogador]`.

Para definir a base principal da sua facção, use o comando: `/mf sethome`.

Para teleportar para a base principal da sua facção use o comando: `/mf home`.

### Leis da Nação

A ordem é fundamental para manter a integridade de uma facção e protegê-la do caos.

Para visualizar as leis da sua facção, use o comando: `/mf laws`.

Para adicionar uma lei, use o comando: `/mf al [descrição da lei]`.

Para editar uma lei, use o comando: `/mf el [número da lei]`.

Para remover uma lei, use o comando: `/mf rl [número da lei] [nova descrição da lei]`.

### Território da Nação

O território é um componente essencial de qualquer facção, portanto, é crucial ter o suficiente para sustentar seu povo.

Para reivindicar terras, você deve ter a quantidade apropriada de poder.

Para verificar o status [se está reivindicado ou não] da terra atual em que você está, use o comando: `/mf cc`.

Para reivindicar terras para a sua facção, use o comando: `/mf claim`.

Para remover sua reivindicação sobre um pedaço específico de terra da sua facção, use o comando: `/mf unclaim`.

Para habilitar a reivindicação automática [reivindica terras que você pisa automaticamente], use o comando: `/mf ac`.

Para remover sua reivindicação de todas as terras da sua facção, use o comando: `/mf ua`.

Para visualizar um mapa baseado em texto das suas reivindicações, use o comando: `/mf map`.

### Guerra

A guerra é uma parte inevitável da prosperidade e glória.

Para visualizar uma lista de facção(ões) com as quais você está atualmente em guerra, use o comando: `/mf info`.

Para declarar guerra a uma facção, use o comando: `/mf dw [nome-da-facção]`.

Para oferecer paz a uma facção, use o comando: `/mf mp`.

Para conquistar a terra de alguém, use o comando: `/mf claim` em seu território. P.S. [Isso só é possível se você estiver em guerra com a facção alvo e se eles tiverem mais terras do que poder.]

### Alianças

Estabeleça alianças fortes com outros líderes e cresça juntos sob uma bandeira unida.

Para visualizar uma lista de facção(ões) com as quais você está aliado, use o comando: `/mf info`.

Para solicitar a formação de uma aliança com outra facção, use o comando: `/mf ally [nome-da-facção]`.

Para romper seus laços com outra facção, use o comando: `/mf breakalliance [nome-da-facção]`.

P.S. [Aliados não podem declarar guerra um ao outro | Ambas as facções devem enviar um pedido de aliança para que a aliança seja formada.]

### Em atualizações Futuras

### Vassalos

A integração e estabelecimento de facções subordinadas são críticos para o crescimento.

Para jurar lealdade a outra facção ou aceitar uma oferta, use o comando: `/mf swearfealty`

Para fazer uma oferta para vassalizar uma facção, use o comando: `/mf vassalize [nome-da-facção]`

### Feudos

Feudos são sub-facções ou divisões de uma facção que os membros podem criar.

Para criar um feudo, use o comando: `/fi create [nome-do-feudo]`

Para convidar jogadores para o seu feudo, use o comando: `/fi invite [nome-do-jogador]`

Para desfazer o seu feudo, use o comando: `/fi disband`

### Comandos Básicos:
Comando	|Permissão	|Uso	|Descrição
---	|---	|---	|---
`/mf addlaw`	|mf.addlaw	|`/mf addlaw ""`	|Adicione uma lei à sua facção.
`/mf editlaw`	|mf.editlaw	|`/mf editlaw [número da lei] [nova descrição da lei]`	|Edite uma lei na sua facção.
`/mf laws`	|mf.laws	|`/mf laws`	|Liste as leis da sua facção.
`/mf addally`	|mf.addally	|`/mf addally [nome-da-facção]`	|Tente se aliar a uma facção.
`/mf removelaw`	|mf.removelaw	|`/mf removelaw [número da lei]`	|Remova uma lei da sua facção.
`/mf autoclaim`	|mf.autoclaim	|`/mf autoclaim`	|Habilite a reivindicação automática de terras.
`/mf breakalliance`	|mf.breakalliance	|`/mf breakalliance [nome-da-facção]`	|Rompa uma aliança com uma facção.
`/mf chat`	|mf.chat	|`/mf chat`	|Alterna o chat da facção.
`/mf checkaccess`	|mf.checkaccess	|`/mf checkaccess`	|Verifique quem tem acesso a um bloco trancado.
`/mf checkclaim`	|mf.checkclaim	|`/mf checkclaim`	|Verifique se a terra está reivindicada.
`/mf claim`	|mf.claim	|`/mf claim`	|Reivindique terra para a sua facção.
`/mf create`	|mf.create	|`/mf create (name)`	|Crie sua facção.
`/mf declareindependence`	|mf.declareindependence	|`/mf declareindependence`	|Declare independência do seu suserano.
`/mf declarewar`	|mf.declarewar	|`/mf declarewar [nome-da-facção]`	|Declare guerra a outra facção
`/mf demote`	|mf.demote	|`/mf demote [nome-do-jogador]`	|Rebaixa um oficial para o status de membro
`/mf desc`	|mf.desc	|`/mf desc [descrição]`	|Defina a descrição da sua facção.
`/mf disband`	|mf.disband	|`/mf disband`	|Desfaça sua facção. (deve ser o proprietário)
`/mf duel challenge`	|mf.duel	|`/mf duel challenge (player) (time in seconds)`	|Desafie um jogador para um duelo.
`/mf duel accept`	|mf. duel	|`/mf duel accept (optional:player)`	|Aceite um duelo, ou um duelo específico de jogadores.
`/mf duel cancel`	|mf.duel	|`/mf duel cancel`	|Cancele um duelo.
`/mf flags show`	|mf.flags	|`/mf flags show`	|Mostra uma lista de bandeiras para você terra da facção.
`/mf flags set`	|mf.flags	|`/mf flags set [nome-da-bandeira] [valor]`	|Defina uma bandeira da facção.
`/mf gate create`	|mf.gate	|`/mf gate create (optional:name)`	|Crie um portão, opcionalmente com um nome.
`/mf gate cancel`	|mf.gate	|`/mf gate cancel`	|Cancela a criação de um portão.
`/mf gate list`	|mf.gate	|`/mg gate list`	|Lista dos seus portões.
`/mf gate name`	|mf.gate	|`/mf gate name [nome-do-portão]`	|Renomeie um portão.
`/mf gate remove`	|mf.gate	|`/mf gate remove [nome-do-portão]`	|Remove um portão
`/mf grantaccess`	|mf.grantaccess	|`/mf grantaccess [nome-do-jogador]`	|Conceda a alguém acesso a um bloco trancado.	
`/mf grantindependence`	|mf.grantindependence	|`/mf grantindependence [nome-da-facção]`	|Conceda a uma facção vassala independência.
`/mf help`	|mf.help	|`/mf help #`	|Mostra uma lista de comandos úteis.
`/mf home`	|mf.home	|`/mf home`	|Teleporte para a casa da sua facção.
`/mf sethome`	|mf.sethome	|`/mf sethome`	|Defina a casa da sua facção.
`/mf info`	|mf.info	|`/mf info (optional:faction)`	|Veja as informações de uma facção.
`/mf invite`	|mf.invite	|`/mf invite [nome-do-jogador]`	|Convide um jogador para a sua facção.
`/mf invoke`	|mf.invoke	|`/mf invoke [nome-da-facção]`	|Chame uma facção aliada para a guerra.
`/mf join`	|mf.join	|`/mf join [nome-da-facção]`	|Junte-se a uma facção para a qual você foi convidado.
`/mf kick`	|mf.kick	|`/mf kick [nome-do-jogador]`	|Expulse um jogador da sua facção.
`/mf leave`	|mf.leave	|`/mf leave`	|Deixe sua facção atual.
`/mf list`	|mf.list	|`/mf list`	|Liste todas as facções no servidor.
`/mf lock`	|mf.lock	|`/mf lock`	|Tranca o baú, porta ou portão clicado.
`/mf makepeace`	|mf.makepeace	|`/mf makepeace [nome-da-facção]`	|Envie uma oferta de paz a outra facção.
`/mf map`	|mf.map	|`/mf map`	|Exiba um mapa de reivindicações próximas.
`/mf members`	|mf.members	|`/mf members (optional:faction)`	|Liste os membros da sua facção ou de outra facção.
`/mf power`	|mf.power	|`/mf power`	|Verifique seu nível de poder.
`/mf prefix`	|mf.prefix	|`/mf prefix [prefixo]`	|Defina o prefixo da sua facção.
`/mf promote`	|mf.promote	|`/mf promote [nome-do-jogador]`	|Promova um jogador ao status de oficial.
`/mf rename`	|mf.rename	|`/mf rename [novo-nome]`	|Renomeie sua facção.
`/mf revokeaccess`	|mf.revokeaccess	|`/mf revokeaccess [nome-do-jogador]`	|Revogue o acesso de um jogador a um bloco trancado.
`/mf swearfealty`	|mf.swearfealty	|`/mf swearfealty [nome-da-facção]`	|Jure lealdade a uma facção.
`/mf transfer`	|mf.transfer	|`/mf transfer [nome-do-jogador]`	|Transfira a propriedade da facção para outro jogador.
`/mf unclaim`	|mf.unclaim	|`/mf unclaim`	|Desfaça a reivindicação de um pedaço de terra para a sua facção.
`/mf unclaimall`	|mf.unclaimall	|`/mf unclaimall`	|Desfaça a reivindicação de toda a terra para a sua facção.
`/mf unlock`	|mf.unlock	|`/mf unlock`	|Desbloqueie um baú, porta ou portão.
`/mf vassalize`	|mf.vassalize	|`/mf vassalize [nome-da-facção]`	|Ofereça para vassalizar uma facção.
`/mf who`	|mf.who	|`/mf who [nome-do-jogador]`	|Veja informações específicas da facção sobre um jogador.

### Lista de Comandos de Admin
Comando	|Permissão	|Uso	|Descrição
---	|---	|---	|---
`/mf bypass`	|mf.bypass	|`/mf bypass`	|Ativa/desativa a proteção de bypass da facção.

