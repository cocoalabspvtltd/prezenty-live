<?php


?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Prezenty - Invoice</title>

		<style>
			.invoice-box {
				max-width: 800px;
				margin: auto;
				padding: 30px;
				border: 1px solid #eee;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
				font-size: 16px;
				line-height: 24px;
				font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
				color: #555;
			}

			.invoice-box table {
				width: 100%;
				line-height: inherit;
				text-align: left;
			}

			.invoice-box table td {
				padding: 5px;
				vertical-align: top;
			}

			.invoice-box table tr td:nth-child(2) {
				text-align: right;
			}

			.invoice-box table tr.top table td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.top table td.title {
				font-size: 45px;
				line-height: 45px;
				color: #333;
			}

			.invoice-box table tr.information table td {
				padding-bottom: 40px;
			}

			.invoice-box table tr.heading td {
				background: #eee;
				border-bottom: 1px solid #ddd;
				font-weight: bold;
			}

			.invoice-box table tr.details td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.item td {
				border-bottom: 1px solid #eee;
			}

			.invoice-box table tr.item.last td {
				border-bottom: none;
			}

			.invoice-box table tr.total td:nth-child(2) {
				border-top: 2px solid #eee;
				font-weight: bold;
			}

			@media only screen and (max-width: 600px) {
				.invoice-box table tr.top table td {
					width: 100%;
					display: block;
					text-align: center;
				}

				.invoice-box table tr.information table td {
					width: 100%;
					display: block;
					text-align: center;
				}
			}

			/** RTL **/
			.invoice-box.rtl {
				direction: rtl;
				font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
			}

			.invoice-box.rtl table {
				text-align: right;
			}

			.invoice-box.rtl table tr td:nth-child(2) {
				text-align: left;
			}
		</style>
	</head>

	<body>
		<div class="invoice-box">
			<table cellpadding="0" cellspacing="0">
				<tr class="top">
					<td colspan="2">
						<table>
							<tr>
								<td class="title">
									<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAASsAAABQCAYAAABBNlMCAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAADM8SURBVHgB7X1rlFxXdeY+t6ofena1JMuWJUslGxC2wZLDEAzxozVZBFizAmIeGZOJIwlCmAxhLBHyJyHjVpJhzSIE28BiVgKDWjyyILDGsmEmGK+gFtg8YoJbNhgYsFV+6GHr0d1St/pRVffM3mfvfc65peruUktqdcz9pOq699xzzz236p6v9t5n730MtIiDW3aXCp2FLcaYTbi7LoFkE5gU0jqUTWLA4r9CkoBNLYDB/TTFN3w3gEewiP64WgQstykkJpHW6XysT5VxK0kKULd13se2qTSl9uioodrGt8SNcx0qM4B9oHPxHTJ1XUXwvZDTsVYFz6pgQQVS019IqvtXfXF7BXLkyDGvYGaq8Nztn+tB0rnLGrsJyaPkCMgK5RhmohSJR/djInCNJ8bvg2UiS5FMElOQdqzbRtrz10wdkRlgqjHuWAJMbExIodt6LMW2jGm8sYictC0iMmMi7rK+a6C1rB0oFJN7oJ4TV44c8wVTktWR23eXTbHj7lo9JWnKSyj10QmoDo6CnWRyIcIx1RRq1SqTCIs4AuukpzqRGdVDyYsJI2VJjEjGkxsTGXeKCdFxii8mKY2YEfkv4eMiiEECLMGlqXXHXF2RxLQR014EaMOaJnFSHZUXF7VDsbQQTFsi9UCksUQlvwpKj32r/+6OXZAjR45LiqZk9fxv70YpqnifNUkZRz1UXxyB8cpxGH9uCGy15iQTRwwmqGNedjEqpUiJCVzkCKhArOLZLOY1LxmxZucb8pKQIyuVlmQzcX1JM/WonNRRUk/9Habhbr1kKIcSJK22lUugY3U3tK/pEoGLiM26+0ugUJm0k5vX51JWjhyXDGeR1aF3/N2WFOq7kWZKZ352FM78+Aikk/VQ2QjpOHWQCYZUsIJT78J+TD6x1BIMWJF6SBoenUzkkrLa5qSnNOpoAsACmg3qZkaKi+uyZMVkaYTYggRnxFbm2oo+BapXWNwBneuWw8INK1HiKoSDBoZMLdm85sv/aQBy5Mgx58iQFal+9aTtsXRksjT8/YMoUZ0CZQRvqBaDtm9AyUvVNFXh5KA3jjspx7DEE1jLk592xkltfr+BjRKWylJQicp66cpZv9I0qJI2e3ehy9K/RCS36JY8ORoirXZYdP2V0Ll+Bd8Dk2ROWDlyXCIk8U4NivvsSLU09M2fQe34aZlDY0JI/fSZkYGvhmlmBWdktyaSfIDPU3GLSKEupBUpjzxrCGKcgkBUhmupSugFMulGKvWdJOekKCYgYyAjLclVmJesEBqwmhjuApzoZqQ9R06jVTj1vQqM/uiw3LYjt5IppvcdRFKHHDlyzCk8WR26/Qt3paMT5cFv/hSqo2OsxqlsY9mozTNlTFB+qCtxiWrGu55RAKSMyUvsVUoIXsIBZ3xXtY0R5vPoemREr6dkVOc+eXuTMpYSWYNEZRO5j8QE6U6ILtjWiEjrfLrcm0pfo08chlPfrwg50swklIvQthty5Mgxp3DDmiSFNlM8ePKrP4La6DhkjEEJq26J2n5U0vKkE6bsInWJGzexSwB4actN6gkZeKFKfLO8cf2sXkJQ2WQbTMNxvWhkwG88F6L+aRPBFtdw2XBrsBBVwgXXreL7xFfdWjS439EPOXLkmBM4yarNFLaMoLpTRaJS3ycHw5KUGqlVLVNyUX8nt22iWbzIYO25IiKNFKIZPqt17RRElZWcPOkl3JiRAk80aZhBtHIxmwaVEVS6guCXZZsQlfYf5N5JHZw8NgIp1q2zcesuyJEjx5zBDfnqSPVOmvVT25BKQI5A1FgOKoWI2iYs4WbtADyZqduA5w4IdiErdqhgj/KWKy+xAEDGVkbvaV0s34nh9lRKAjHGJ3qecWqi9dc1jlzIHmVltpLtVqxO1onLpDw10nZmn8v4ZYAI3Z3PdXse27K7BDly5JgTJAf/w+5NIz96vpxaK5JEwoOdxR8/8FOxSKVCLHXL+2pHCoMaAmEY8OE2qdqCDHmbG+e9RARQN+BJoRaRBr1T0AyVWbRn1bCM63K7RCg1t43HaIZQ+lTDV933x7hydy4WpAlfs2a4HOS6jrTwxdcLbdVBy+llYQIlqwmcIXVkhv8WdRa2QI4cOeYExbTY1lM9PqrGY1aZIluS9UbxKB7PsPuB9z43YtixLOmom0Ps7hCCZGxkbmL9y7KxyzVBpOBCAkVqChKbMJXY0IzYyOqRu4Jr0YhXvNRhKSorEbq7UKdRaVPJ1Hnc6zH3eUBwh8D3M5UTsHjlYrmn+ibIkSPHnKB45unjt02OTnpyismAeShSszS+xYpdyjIZuegV0Bk0EAJyocTuHPa/AjGuG56hS1UlNN4G5uIDxbhVl2MgfOJON6oCmmBkT4L4FgzmJDlZH0jtDPqG70XJl2YfNaQnldlG6mfq2xRfrthfAjF26BQstKq+Qhly5MgxJyhWT4yU6zoWhYt8tgSRrMLsmznbEG2NxOhZIRw1yCMxeJILdZVVrI0Jw3h/LQC1h6lkZf1Mo/7xzqkWxBlV+8+ddrWTgvhSCaFab5pnQrZp5txU713iDomoqlRHMklwvCIen0yhdqYKhUUd1MZGyJEjx5ygODE4VrZq2BZisD5cRolB1LrouMYBOsnDmsh9IPgxOenJsCpn2I0qWO8TCJOOkJ2Rc1cTtZIIz4gqmFiWvpTMnBqocpYN/XQg9TABOVd8s0ziDfJO2nJhPiBtiSSZZm10RgnP3yLarl4cgc5yO+TIkWPuUKxN1EqqZrkBqTYdq35JJpT5ejzYwcXrGe+jpGEvJtGQGZ5JLC5bCKXbroFCV6cjhbFnTsDQ/qchxAgKoRWEoEiisUwYRiU6UNNZEtoWSa9Y6oTu214GhaWdUBseg7HKSRh9/IjviyNeG7I0BKdV4wnUZ9pyRYmQoCsRIk19eGFtsi6qqSlDjhw55gTF2lgVAILLgtquVB1yiKURtfMA+ER7ajqvgwz6lPfJWH3F+34Nrv6zN0Fb9wKI82CNVwbh4F98A47seZRpwUta1gtfxpoQ5qOyj5ut5Osuu/UaWP/BN0LXrVcHtwe5xsjjh+GJf/9ZJMaToFKYk/QSvhDbtfRmwbXrUgBam1GDQQgzgQI4iqJJgFoa9StHjhxzgYTsVW66X9QeN12vPkjx1D+5BegxIy4H4ovkpvkT2adjCZ+79s/eCBs+usURVSM61pXg2v/1H+H1v/gT6Oq5hl0IQF0fEt5Pgh+Ucx9w7gyoDpYWwMv+6q2w6RvvcRIbQX3CFEs2robXPnontK9bJm1Jny27MdSEcPmaLKGx60TC95MGN4w6MlcV9+h86kMNVUwitRRy5MgxVyjW1TtcYKOZMrdvbVC9xC5lGx0RIilIVacF67thPZKVtnP4c/8MJ/Y/BW1INEs2roLVd/wrd6xzXTe85qH/DE/95UPwi794SK4P3jiuUo4a8DvXdsOvPvQed56CpKcXv/oknBo47Npf976bYQEeL3YtgOs+9Vvwg9/4Gy8FyjSftJfIvIGR7KRIVDKLCFEANns9qGRnvONoJodNjhw5LiqK9Xg2LjFi2A6pgtmgnvVlsqojGZM1xIsti0hr1e++1tWlYwc/9m34yfvvl7a53s///CF42X97I6xB0iJcg+pcB0pBB971RXbbcoQp1xPi6Hr1lfA6JCpVKWtD4/DkBx6AQ5//IfhsfPj/yAM/hlse3Ylk1QHLUWozSGC1oTFv42IYnjAQsGOq1SPi64V9SKzPsaWTByxBAsTplXPkyHFxkZAaVAP2+ibiYu/shNWkhNU6svNYGaDqnW6dVGJYbTLs/c2qonXHOtdxJAoN7mP9TwXPdGnrNEpDA+/8Ehx495cd6RDW3PEauPnRHZAgGZEa6tp1hvwEVv/ua5xEpSrl8W89Df/4yv8Bz37uh06NrUn/6H5Gnx2C4YFD/voFJKsQRiOzfUmk8oqsyCpnUDvpXtA8JffHddmLH5z9KpercuSYOyQu7IUGaSLEA0JS+E5hLBxqEl5VZ9+ikBXrXqQ2BVuT8aEqo88M+oss73mZIz+2O6khHq+FJFTZ8wPY96v3wBmsT8TShbammx78fWeXcuSDr1V3/ApsRHWuXYjqqY9/G77zxr+B8cEx535gRTWrAxMNqYjLb7vaX38Ejfk1GwiHCKkuSfxcX6zx29rHEDsIQlDG970uKmQtVwNz5JgzJEQ4bCTnQehj7ACcj5PGxdWdCiSk4CUsJQg1urMxms6pfPaf/UWuxhnBDajmpUImTpIDNguRujWCRPVtJJ9AWFfCa7+y1fVpyQ2r4UYkKlVVn/zzb8CBD3yN4/8SvnZN+45tdq5fBm/4xu/7a5Pk5ckHhKQSECnMiAQFvi2q52MUDd8zzZc6Ik6DZFUDm1GNc+TIcXFhvpJ8wDp/qZDKIPgeid8VpGyH0pxWpDSRlFNGe9PSG65QU5G4PIhrAZLCZThTt6i8zBvZhx8/CkOPH/J1A9jPiuquuGW9JyZS9bpuuBKN5p2uVhXtTofRkB4v+eVTzYjf1ZW/eb2zVakrw7H9T8OZZwfFK8JmU9Bg4dATRxyxTqKUxt7qaQg1ArWv8/USsWt1XXcZLN6wzJHVrV9+V85Y8ww3lS9eJtfvVbZXekq7S+MlKDWWw0sMj8GOMr71yO7AjXDPJU3nXTyCA25l5P2tntwKHydo1dnbupm/DeSWgLNurk7kPxW7DzTmpirhLGAXkVtUt/EcbY/KV9x6daa8vXshrEO7ViOmaoOw4tb1+Hd907q0vQ7fyZn0yb/8Rx/y46QnDcfRewZRCVOVNMWnNce8Q7FmduO30wMXHLYf/2yuLyncVazZHaEY7sW/O+Clhx585HfTBj7qu/Dt0pIVODsVr14DsgIyyykiWoHM+CXqOc5hNm1dnWc1ZiPJJev6MLXw0UhuuqxWTGQ+1jAimcY6cRuN5XH9xvZoe2G5m32wouXF1K3B95NbckHYFjR1Ts5Wv0QYqtXs9ltW7d6Cz40nJhwilUee3/pSJKp5h+JRHG/qsaSqEm+Lx5T6VoXAPYg1Rir/6Scehuce+HHGX0uDj9WjQE4N+xaiEJeY4PigpncxBQ0kDtf2bUFEhDYQiudG9ZGycVpmPrDhD2+Gq956LbsiWJl1NAb8omNRn7lvnHY5cYZ162YodfGJHC99WJvuchtJcndcXq+mmyHHnKB4DAfdK1JeLCER6ck7P0KUkwo0e4Es0uDJzMCTH3/EGckzhh6BFoX8VyGcxUFyR8VrEap9SGMC3XWszXIh/ZG4PzuFgONUN+BVobkjKi0CnHpmyJGV3oNmIM2Sn3VxghzEzATljPEgyQRza9UvBfDZ7Hvk0PZ7fu2qPX34tZejQ7u+dzRf+HauUBx1xMEpEfxUvOE1+YwazsWZUwOBKQd5Ghls4pk9dzqEbXKsrCMZuiZUPJJ2HDlpTKDE/KkUVIfAe5p3iglEJK8kAU0HY01kc4vVP6HJuvcXDd7w1oRVyFx4jTHR7J5LK+oDspXEuG+Jz2ya+4S+9EFqXr1md71h9e5t+JhtjcsfeW5rL+SYMxSfEbXGRkZkXSRCB2RQvRLeTzQ/FGPRum6UVAYh6E7qek4+S+L9LoRnIhXLSXMaUAzgs3LaoCdm2go6qYn1UJ+kT0+xunyzJ8wgFbru4fESGvq9FCX+U86P3bLTqlFildAca4Otisqr9fol8VzAGRqahWpMp7wXZ2qG4jp4z1ux15TJtExl2O8DWPbYRrh7zyza78f2Kw31eqgefgSa06uC17gf6+2Fc7ufHuzXxrivgiHqM7BRtz++v7lEIYVeejYKkNwVCfBD56v+RZ/zJvwMu+JjeJ1n8K0feAZuaJo2eqDhM2v8/BueBQd8uvtR49jf+J026VtPOAc2Yvm2JtUr8l6GqTE03XMh19s0QxsDxeciO40NVp+MPSkY28WeJQKYSjD/5qH3wPEDh2ByaNyfG1LNaLrgyBAeGcGlEijbtJc6ob1rQch4YELiq+yMXyNThLrRZTLqZYwl68LMs7WyKI5b6FQSCEYrNntpTdipKtkXwCQw16CHF79cesDv1DLLD7Z7qA/AzjvxE+jFV2ZqHev30DcxADvwGNyL7dwzTfugs0DS/nZ866NtGiByrNx4LpZvw/b7sP7OmchF+4mbpayCn2nPk6a0u2uqAXYxQOrftw5t23PzVZ+9D7fLoTydtfpH7gDy+fVMVUee7Lvozwz3PdDwXVCdvXIdJCkggt3R+Pni/ja6xgF8FjbCPbuatFuKv3/BFnP2jxi1/HYiIrxer2my4hM9C9qnqSDPHP0o0XN9d7M2yG2ieBw/mlEKjwnW6LhSyGUONjOVH1IN80e7/IYrM/t2CkNS4yzdVG4LzWb0GutOdXyqmcCpzp08NeEcRHUGgCTIVHO7W694QmRmc7OH5tJ5sPfiayu+MoSED/ZuehAb6lYgSyw0WO7Gum+TB+0sUsGyPjxO7ffE5fhw08Pf21Bdz9e+bJPPaTs0AQ0iPH4f9rOnyeEheZWbHKN2t+H5O6ci2gsJVf9uXrPnTnwGokFq+8l+BbNATNDncJq772bEIoN8Ox7fF5cLIe6z00sqNJZ7sd0StrsTZgEh0b3Sl175kYsJq6/V70qew3vkh3hrdI3t9DzSdnEciepZfF2T1ryNxy2gkEakISoQO0XyAB1Ao/qVt10NS9aGz70ZSbgLNilrhWgyH4y1TV0WGt0amrXfbN8T1fAEDHziu+yRb3XtQpX0QFRCE3fEebaz4f3SkBV9sUgmFWDR2QEfurux19uA1Sfy+yHVqV+P00OAbzuiB4EkpH1YvrkZYWEb+01EVqQCRERFv/Z7IFJTdIAADxAilT3x9aVOSerEC22gdGD6UC25P5YeItWAftHfJu3St3I3DYrpBsHDz2/bDOeIW1bvOYgEVdZ9/CFSsu3VMkdgVbsdZgEmekdUiiH5DGmwZz5H4HvN3LcQCzSRhAYarxV9D/os0DUqQm5Uvk1JhSQvLLs//q7kezD0nZvgZ+VJYyoIYZVU6sdzhuE8IGTY5+8r6bzLvj2twq1pDRpvWdU4b5yG2KAdEYhoc+8b+5A7k+xXfa/4cJYgjCp7BrK+V9HipvFKqPGWLDCh3fEGdjFS+cylcp1Y6tV7+Hff+D1Y7RxEAT6+4IOgOdVBUhu7GclI3XV98upkpNoiiXdf2w2lV5RcH7Z85d0GLgGQrOiB7AHgL1Uevv6ppCWF/HLdB2Hw39PslzUW66X9rbL99qk8mYWwHgOWHPo3wT0Z0hBC3aH7It639MvboGbQIFx/oexYKD1Ru71R0a6Hn9/a20hgaKrd9q1DW/fAOUI+l4NRUb8M/koL52bUKzxvc+OPwABpeoyK4R+SXiGp3qk+o5iIoMl31VinFbKSc0pyr/QMnNP31HBuBfu0Pj7ujC5PmILMcMlLA5rB+swHGs9XBeti53R9vpqNsiNE4KwG1mducLGFNDNHKqSJ4hClXOP7fBYIgLD+n8YegmRNkFk6lyFBkuVxNgXjkuZpQj0NsHZrBCZBQnIxgcb4JIMcnAwurjFOAliHbHaGsK6h5LSaJ35W8ks2QA/cTA8GEQ0RDoj6Jr+sPS20rwNlYJq2KxB+6XtEOnKgATtbopK2dQASqN1tcAFw0xUuNKdX90l6IqIiAouJiu1X505UBPlxUOyV76nSyrly3/GPyZZpqpeEqEgi2THdsyDE0y+7PSJ5nzfomiIxuv4UwNx2DqfTvekz0994kOzecAj/jNIATowQUSqD3YSAYWN8VgIlNF1wtJ6GGT2FLhoaSDBkPHD7SSAfDoa2fhFTvq5kfVCytDaQkCNPw6RmbSBYE9La6Lb2wdqg+lkhHEemcV8AfAZU35Z8BkxUwU3Dk+D8QEkIqCUIYenAb7QzNG0fqb63lQGG7d4f7cYDoDfa7rtxdnanvbphwizkeaGtkOyL92mWrxmB1et2F8wC8kOgn0OlgXhaQvxZ4X1PN/idREIEBy0gIhVCD1w4+O8JBZ4drZ5kspNGZ33eCbkgjGG1J5ICZyRQCcVYGcw2Q1IWdBVlG1KmAGdpCFc1ISWMlXTIYIRwJNWKZbJw7yZkOagZIRNZX5CJiG1mKi3FKy5niE2IK0hKEseXqGLHiDNJaNsa71d3mqX6UiUi5VlPcKlkE7Uwr8Jt+mYxS0YDQH95N8VSUBNUZnJ5iBD/mpd1Ix5kzR7EFlGO2jgvewjhlqs+e3csPYE4eTYSGLkvnIfzp5eEWiX8RoidSTGt5Ix25ZbJASLp5UKRP0HU1H7Z7Znh2XIQyU5Jvb/Z55SogecH5L1uopxOVqUYUY1slOvJCmHYIMVYy9P+aqfyKpuTzJgcNPGdppFRIkot+NQ0gSw5CWCcI55zoKtaKOqnLEyqy9CTVBjfgyM55zwWpCCWyFRKEmL1ZCSSZcNn4dRTkais7/f8IKuGX8iWIOK6SkFqzJ6q/fuhdVSi7TL9iYzGenxTwwCcEeIyEUuAe+E8cPOa3T2NMX7N1D+8+Xtnq/4RYhLAH7fuc1W3dPY0tGf6pqt/A9zd8nfVQAhluIBoeGZaIVBfZ6p7LCq5VNBuNdy2EJZUx3iFYvFZ8h7c3IratCEYoQ1oaE62syYazMH6TU2ksSNAxnifQPvSDpgYGvee5q6K5IDv7OqE8eFxMJlrg++cum25VZ+lbTbmZ2cLU5nxBFla3krITwrhJp1zaGyBt8x3Pmto5Hc1DzBbQ3Ml2i5PU28AWgT9qtIMI4iBtUnbZR18aBgeAm6biPMZqV/Rivjz14XPynoZ8D1abtlPrB9mCVLz8PvdHfk1OydPIjBoUP/aRtJeOD+UdcOID1HDfZOz7iDeZ6OkWBZplMhNJZOZJNwKzB4zSj/niD7gHxedHeydqqIQ8ttkd8p7LNIf5gUL32nrhF9HsiK4RT4jd4I4H3uiGQm8dBG+dZ3+rwnDubTCkHgy04wGTIjsItG9rhve+ql/C5ffsAo6S50whLOJA5/7IXzrQ/scT7zuva+H2/70X0On5F4/8Pkfwv7/vg+GK0PR7B/3z/gVoqVXiSQNjPpYl/p+lWVhOe8Ma0x0rtvIfGh1kfTmUYqYC0FWFwzTEYnYyvaK13oP8GDuMU3rxt+aw5AQVS+cBwptSW8c40dOnp3jSFiLMgTm7FePDG2/IDOOwPYqsisS+fQoAeP7lib32Qz99hzskrPABSUrcZPYI0RVIsl4mudiWsO6ohisOQYerU3CzZ2LoTg2EgY9gHct4NxWPFPohBohAeuZAbwqmHoxR4gLtK3gH0+TaaX13XDH198FpcijvLu8DHo++Ouw4a3XwQsHjsLG38lKzpvueA1s+M3rYM+bPgNHHz+qvpviWc998a4RKYjbROSTJX5kBTdJECXbs7F7RbieiwnUpe5TnaGElwLKuoGzNoNw8ZCxY0U2DW/kF/WojCS2jtQl/MrW6TGxT5Eksvd83RXOivGLgpTNxQ1SLgMbv+k++rRwuvsm4LM40OiD9i8IpKo7o7mo8P3NKpkZDOuKomozNBAn0jocWLIMNk6Mo+W9FiJsWHcLWQ6cVMFkEBa64X1955kyCX0xQX/0qzVLvW1f/z1HVHTe2OAYHH3iKKxCCaujq8O904swjqrh4QOHkci6nSRGKuHtf//b8JFXfsSpbA5uufjEk5Ou+W4hK/3Vpbym7BQRj5P6HMFxTi+3wo3hyQGncRZ0lS4zb2xWwCpSH5wjTGT0RknxGbh4qMTXJLG/kXRkELesbs4GpP7FMX5zFKRM91SWbSKm/vjgBb7vCyUFXhDQjxKqvP3Az2dPs++9FcO6ImmMmxsYH4VTXSu9PxHnTdd85xDNwsWLkgY1S9XAuhrhE+Nn8vwKN/K+7rb1XqIaRNXvw9d+BD71G5+Gj73u40hMR3yfDj9+BD5+0yfg02/+DJLTR+HJB37iyrvWlqB8y9V+YQk/q2fFOA9hFlPJyngXjCiHvAmuGc6Yn1h2zfD+ZuDSw7jZSreqs/HS5XyAicITWkVDytrKjRcxZa08oP2yS4G1b4PzADkrUmhRK7NMMZz6FxnPaZbPvSOBxfUuZI4qfBT367aZ2UVkWtB3NsN9zyuyIrRgaJ/RsK4oNoapHJk4AwcWLIVNi1fAwpHjesGMNzqbnUWySFQdTDN16lFvNXzFG+Etq5Prbwlpi7/87v8NoxIIffLZU/Cpt+yG//rdP3DHPvtbX4ChCmoCBZbsHv7kd+E6SZy3Ad9/8e1n3IXqoBkWIKwJqCpiZINz9bCvVZGWOElXEiQsf64NC76m1hvyeUYzzUhklxg9M9gEmqE32u6HiwyasTTe495SDNj+2U7j66A/F5WQY/yy6h8FKTfJUeV8r25ZvQfOBd8+tHX9FIf6QAzNwN/TlnPNTEGIQpUAspMX8x19MI2hPZLuZ3SPcfoTq25B+jhw+gS8uHQljHYsDX5U9TQrhRhdZ0/dEkymC2rXCZJOcKbUNQbrsQEfxCsc2LVgBFXCD6EUdffrPwknnxnma6bBfyqGukiACZ7udfGOVxeMOMYvVTua3oMs6Br8xsTTnvysyPcsNewrZk20nBcwwc0PVLDru1t1B6A4tUgaG7Kz93tqGeIxXZFdNx0/C/eFTTJgy/YcnCuncvJsVP+i4+VzfU11bXERiR1wd8/CfaEc3feef0n2q0aP9jhaQlLOlGW3f6a2UGyoymYw3oymNXhyZBAGl62GifbOQE4QPLjZiVK9ytkfKkhewTkz9nz36peUnUTVT6Wx9beWvR8Xq5pMKqOD454w1U+rOwqePvnssFdN1YFTV0zmNth/DCyEwGchKbpzR36JFX+yQMbemx1M5Mke/MHoXy2tw3yA5QwH7pf3cdg5rYol8Xm90blzlnalYTbLEc8B2DmjCktShRAsxR2WbRTt3wrEydM/NBqk3Kj+XUTcA1mifozup5UTJT6P7psIbuB8Z0IvEeLIg7ui7SDptvCDWeT5dxtOF3Xp52cGYS2qg/XLroElw0dRJTwpdKZzhwY0LxWIvSjOTlB3TZnQLEAIODa8feBrP4UtqPp1ojH91ve9Ab7/ucfgBBFYpDKaRGcVWZ9bhsb1N/7pZk+Mjz/wM/Gsh9AvqyvxGF8aNDyObfS1jfpZycfgFs2wspQNQMi9xWekIhHWacmuQgHmCSokadCvNs4c7UW7xoDYCvrlOOU22ohl22w0AyiD/p4p2ixNsT1rkF1MUprsliIkHtsnOba0v0Ohz2YdHt+C25us9EEki15oEc7JM7pnbODehw9t78fyfZnyiwiZxt9sQjYEuo/eAZYs+uXeGz3/NVWK97GyTVwXGuxXZbh4mPUzIIZ2sok6tw3ps1OLpUp/Kz+YKFnVxEQj4odgEgfjo0hSk7g91HUFnF56WZB6THb1YpWE4hVjvERigze8LsuusYKjw+PwzU884q7XubQD3omzex20EjOEgOHgSQ/u2Dv//nZYjjOCdI1/+sIBOPbsoA/ncStFS4A1q5w29A2yKmeqqqgBCYZW9ZGlrJoa3L2UaFw8ohUpyyYJ263mCUjNilQjetBJEtknr/tEmiprfcuBxL1TtWeCk15m+wL180ZocEglewb1M9tnS0TaA4GoyMdqG7SIpkHKh7bukCwLPTCHoMGI/d9ss9EGZeB0LfF902u32He87xF9ZlMM6C1xezMFpcdoyPw57bnn+wzYyNCObR0UaVH2pzesKxIOCVYjuJxseFAfnxyDn48OOkP08JLL4PjlL4fJtgWBFDSIGHTGjeEyIgB4clK1zw1wgBBAjK99n/gePP/4UXfemo2r4H0PvhPaSh1B5RT7UgeW/eHXt8FqcWUg9e/Lf/wPUs/6MBsfYJ1EKh3foFc5VWWk/nDfbTbQ2hEYk56VcCEuAzcb6PrvrPbzxmblQFIS9mo9TK//64M/lUQFoqKUo6KeVtWWVkASFqX/EPW1f4bqJHHstZzxYQecA1oJUp5LENkQ2UakNZORnL6rzVNl04gnGxSt2i5nce75PgNxLKpKVoSW406LYCm0ZanM2mkiOzrEEtL/Gz0JKzoWw+JCEeqFNqiuuBoWjA3DwtPH8NufDOdBTHYSL8gOSeFqqUpdQY4bQenqo2/6DHzw+/8FlqOKt+aGK+BPvv9eLNuNKuGQq7Ro2QK48x+2uWMEKr/7zX1OMguLsnKLiZOYiI4S9pWywclTpT5nabJsRNfjrMgWkH90ls+4f261n3ilZkd8iYTmzB/JSiG/vpvloYt9WKi8JafKlAdS5gGycOEhRve+hhzcZTlckdfAbBxBz1L/xMnT5aiCS4s40FekmZI6hQLfs3P1aOG+yXi9uVk5zIxWzqUfiX7dOZ/PTVThG2V3W0SU/a22UbRIVj7deIMbA+2TVPVPg4fgpu410FkosASysAvG8dU+cQbax4agMH4ajOR2Ur8tdbwMifaMswulPgwH/Fp+o6fG4SNITn/04HZYjsZzIq2duP3Xb2LTBpWvwDJqi4jqr9/SByeelc/U+D/cZ9CVa7LLiGnfCG7hMQPesdXHKhJ51Q3HEoLwrNjKgu2KZghTn1l0vkJIi14tG6Ibzp0zNPhhnTeaxfiRk2cTArvkOJ8YR/ncZuXC0Mq559P+FO1V6B3tV7dpWTxTOhOQrKooNaAqmLRBdpFRcDF1NHDH6lX4ycgxuH7J5c7VqWDZ5anevhCqHQtx20Db5BnY+7EfwbU3XQ5jg6NijDc+vi6QhvFSFa9R6KztcOy5IfgrJKf3fukdcBVKUERaRFJUkbYdUaHq92GUqJzE5WA83Qf3C/CG8SB1gScXIy4NScGA5s5zfmIJhxOxL5eyd+xfZsS/DNhBFOy8JqtfZuDXOJSY4NjZdlo9xNP90FyayDFHaHBG7j8XZ+SiWybdjkC8GAoPfJn7k5ibo+Mj0IGEtn5RN57ERnOKgqbxTnNidbRlfQXJqogvIq/CmutdLE+C0/u0TyojqWjumGF1za1WiCqbxTokZSXjAF/cug9e/45Xw4Kl7XwcX0OrR9zxL/U+CBMvVqGA6uhkver7m8iagyGCWWcpmWQSk5WsyDZl60ygPi4wlaTNGkvop/8iIrTselFNeTYwluhyzB9859D2pgPg4ee390OOS41e3WjVsK4osrQxhq+St1UFB1ELEM2iPXtm0AX/rl7Q5QiKDM5FZyhXgzm/o+XHHSdiKqKtiwZ+odjm26GxT2RFpOfICxxrUjAtFHBq74eff4rPjY4Rca2FlbB+HUt3KRLc6fExOD0xBsdGT8GxMyNIIjUI8euBoGo264Wf+nsTSc/wQhicX56lKreOYiruFiAqshCYVTLLBascOVqGGPW3ym7fOSR0dCiyn9EYG5YlFUpsYPeOnvL+NBrcKQPB5R1LnGRCM4JFYFJRAnNqou5H5BVLYn5bVUqS1IweM36GrhCdQxJRwaWcISIrQGnhElixaCm8bNnljtwODh+Hx188BGeqEyJdiZ+WIBMKJCSkQdYUAM1JG7xolRGcrKiVIPcYu0PkyJFjekRe+IQBO4v0zkVvdrHDOLBpMsJERvHwHkLtLDw1ctyRFBEWEYyaehwByfZM5JSaQGiNdfW9IATWbN+rn0qWiYW1S5fDmsXd8MSxQ/Djky+wFAexfMhIhXY4a0IwxLNKbHxtt+KNW8c+LJRqIYQNNea5mmOUG7YrkCPHPIMstnqn5YBlsjXRDOP22czwFtU0Y9NhHJS0irUOQJ3Wt2eZZlxmUSQsGrgrHWFZP4CVeOoiHZ1FWgaiuix1FZ36ZaOsDkpinCedzndElQRbmZe+pL2i84/C6yHBbLr8KpwUqMMvhk74rsdqYN2KxzoYPzPp1EENx9FMo3XJYwXBPcMdM+LSYC6NzUoWyyzrvpkmV1COHJcCFEYl6ySWRVDot+zUe86z04piyAZKBppTuEHSlYWQ54rt1N4HS06k92dGT7hp/Cs6lwZpKjK+03tbRDBOaookL09k4nVOhnevUjpyMrLkVqQqAnhHUFdXJTWXTE8M+HjsxsvXQOX0EEySz1WjS4bM5pnIHcHZqUQO8zm6ILg1uJAcZ8O33hv/UsQx85JWlnyS+hrKt5zPg5Ajx4UEpWnG8dMvyQMPnI+LhqKYWV49HQJTWArBdiWDl3cyJxrxoXr+zEnnPb5qQcmTUeLVPM7IGatypFUVVaKyHK5TFDtXMZLGXFspePIpioMm2a2KYH2eKWe/kn0lRPeOTPLyZSvhiRPkHW9gpfhpEepWbwC8zYpvSu1z4kiaMIl5R1bDn4YuKHEp1g0UX5XtkCPHPIb8cF7QH0/JFKrqDLkRDKKKtPwsW1Uj4vIXxoachLV60TJPNo77TAjH8YZzw4bzYqQ2atK8WFXU7A6OqIQsCra5bcu9i2oWjht4WfdlsOzNOIN4wxWwYu1S39968HDwueAhUu00C2giqqkRMVPzzbuQnEuoBubI8csIWd0m9TnGLUlXySI81BkRUiR9NNmnrROTp2C0Pg5rF10GnYU2IS1W7QqWpZ/YUJ6q2mfBS0Upd8itX1gUG5G6ROgMY5htFIlLDfxRG87tgWYNkwLc8Ue3QukVSyCWIFNxn6Dr0H3XvcuU8aogHa9ZLbPiEhpcF0DyuOfIkWNuwKvbRC4Kbrv+Io74q0B9kMJMYNCcGiUu2h+vTcLTp1+AtYsvgwWFdjaE2zDV72YMrWZeYDIL0hGTj98XO1as2rnz0qzNK7aBBTJkQqLt4z8dhu4NS6WPFl58dpjVQPGer9ng9GlNGt2gaIiJqsMgaWQk+WBOVDlyzCmKZxMPDUxUylKc+k+uyORy0nqB2MANXgvWExg5Zj516gis6CzBSjS8p+LQWfQuC8YtzOWkKMi6MwSbVtb4XpDA6EJDfZ1FJPLx0pplqYwN8AAP/u0APPB/BvytffdrP/erMBudCnQkJOJVAt6VQW1XIPY5nzK5YIKHe44cOeYExTgekKGMdAYH6CCqSd0ZyUrHdyA5e5ZkRmUnJobhTG0cVi1cjmphMUtOIO4Lydl+VmkkFTniUgKC4NbgpSzvMa9OpJF9K2WJ7tCTJ+Gx/sMh6Z/cJftU8cwfW88Tvj8b7lFZmmcBwadhb1toNJdVBXLkyDEnQIHHVFCqKGtBhnjQ2G6dqNHl9q3/E6SsuL4/IJIIkdWzIy/A8s4uKLUvEqISWxaobUolKut8pQp+tk/sXERUzq4U6saEp7OJTpqSUBr2cudrucR8PjYwaHmpj+0zourZiIyNDwuyuhiGt11h+x1i2zK2Ajly5JgT4DhPh3QFl6x0JLOB6QmXzwnMkrNOVveFRsKKuauGauHRMydgeHIErli4wklZbBi3blbNzQQa6506WfUL9isnKSGxFIzUVcnLZh1O1e+KJJ6iUZcGA23O2C8klSEeI4KT8f4WKjlZf3Oq6ibiBY/XbTOwsKugM4MVyJEjx5ygaFN7AA3LmqCNp+1dorlQyabHxNi8JGNcb7RhcQOQnTgUjNUm4OCpwyhlLXWSliMkI/5RNoTfsMHcerJx4TRnSVTiACrqnzqgpiwkBWO9M+wnPhaQ+uVISlLfpCpmJeDza6XehsX34qQwkNzxWLi0lLAKyw6y/ZAjR445QREH5gAOxa282zAraK338E7TF3EwU1aD7kwDMTe57cZZwqgOyTMnxofh1OSoqIaLIW1waaib4DQaZgyzrg+q8imBBdcHK/5WKpGJxCQuB2FFablX3zdxZZBtsFGEkY2kRqxUuqLoruMCpROXHylHjhxzANRnrj9qCsVMbuvY2THr+Dgu0sYCGcxmmrrg1ay41M01oqo2Uh2D0/gqJG34KvogYQIvdAXeu8m/rDibSk0bGcxTPe7P53OLSQGeOz3kjfeuvi76YES9c4Z2WQtQJEddZTq1YQGMjgUA11zfzuVgK/fev3MX5MiRY06ACs8XKzhY+2lnKrsTQT27AY3uYI8407UzOUduDLFUFs8WBvtQVgqbqFfheTTAHz5zHM6gbauKByet9e9Tvej4hOWUy1UtAzoHpAyiY2jDQruVX8PQsG8V77O0VhN/L15kNfEr6+i6g26tQyS45avbQBeYSJJiL+TIkWPOUOQ3uwvHdY/bshD5VdmM57fC5b+yhyAprMTKCyKJykbngXcobeZEGheSWkivpagWknrYidJQwcRhOs2cPyW8xgRHUFUBtU5ieaZwMYpEI0iMzftiRYrSXcupYSBqBN87FhpYfXXRzUoWElMZG6/mKmCOHHMIXqUzfbwCycYe3CpzcZCEjJ/2D8ockxHpSiOcrQE6M43Guc/jspkwUZ/EWcPTMILGeMowSuqhux6AXy9VVb+w+rPxPlH0pjN/4cVWqWPjo1m10rAfqDp36qKoSlxOFXSzpCxdvfKGDmjrNJqR4d7/+X935hkOcuSYQxT8Vvuv7DfWbsOtTnaYZMfJGFnXBi2ccKTlnCpNB0DsEgAwZbDvVORFpeQFP4ySFr2o3fakPUM+Vtpn4rLBzsQXbCArtlu9ODYiS9zzrKHVRSJ8O0pEJrJrcflV1xTgslWcET4xSeWTX93xdsiRI8ecIpBVbWAIzKteMElhCxeQXSaogMaREZylFvI+ZWtAycXSKjlEWI2JnrJG+LDOYJbQYomMDfEWDfFnYAhJ6wyqce1oe0qESGMySm2QuFIbJC8rDbk87Cka9WuTjqBA7FHOxcqTG/spuFVrXEAQZ1dYe3UBVq8vsNHewJAp2Nf/4KcPXrDliXLkyNEaCpk9+8QAwKuG0Wbz5mazfLENi96TRNefERKixeYpPTKZu027a96rkzD1rOFM5TR7SCriCVIRq+OSWyqBRNa/scY0zCaq+hdUw85iG7w4OsKOqF7dC64Muq9SFWmgG15dhMvXJBIf6Ej5D/72gff3Q44cOeYchbNK7BPfg+SG/SZJepAASlmiCuTkq1vr4+yCbxaR1incp2CXDohJ62yYs/Yb3SKMUY9zpENUEU9Vx9AGdQomcJuIqJ1cH+ScRhVQSazgvPANnK5OiGtC5KKg6h8Z69vRkL42gQ2vKsDiJbI+IElUFt7y6a+9P7dT5chxiVBoWkoG9/q19+Pg7kaCcN7tZ0s98b49yxDv3pG0DJwCWvUZTBHLig0tiEumaWwPGshNXTez5ePpJAxOjMILSFykJlZtHdoKdA3JdAqRnQvfF7V1INmlrq5XJZFoOxcYWFICuPxKAy+/NoGuZcZrskhV/bZo3vKZB3a2vBhjjhw5LjxmnqKD28tJe3sv2oJuwxFeThLjg4AbCcVaLdcc7ibrCkEO8wnFGC5x5JU9WzzKTdZNwh0zxvtsKXFNh662BbCsfQGU2hbC0mK7rEGoCfvQgL/wFHSsGIdOnMRMChbaihCxoCfb/pqt7fr81/64H3LkyHHJ0QJZRSj+Tg9a0Dcic/QgYZRJ6oqXV1cS0fhC3VYiy0hQOHNozGJwbg9uFlGKm/lkTdV5A1P4ToW+UOD04mIHrFxA5NUJXe1tYKoFKHbUYcHKUUg6q5B01CsmsUNo0B8wqRkodhT29O3dmRvRc+SYR/j/qeOVIs0ZCJsAAAAASUVORK5CYII=" style="width: 100%; max-width: 300px" />
								</td>

								<td>
									Invoice #: <?php echo $model->id ?><br />
									Date: <?php echo Yii::$app->formatter->asDate($model->created_at); ?><br />
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="information">
					<td colspan="2">
						<table>
							<tr>
								<td>
									Prezenty Infotech Private Limited <br />
                  26 S R T Road, Shivajinagar, Bangalore <br />
                  Karnataka, India-560062
								</td>

								<td>
                  <?php echo $model->order->event->organizer->name; ?> <br />
                  <?php echo $model->order->event->organizer->address; ?> <br />
                  <?php echo $model->order->event->organizer->phone_number; ?> <br />
                  <?php echo $model->order->event->organizer->email; ?> <br />
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="heading">
					<td>Item</td>

					<td>Price</td>
				</tr>

				<tr class="item">
					<td>Food voucher</td>

					<td><?php echo $model->amount; ?></td>
				</tr>

				<tr class="item">
					<td></td>

					<td></td>
				</tr>

				<tr class="item">
					<td>Service</td>
					<td><?php echo $model->service; ?></td>
				</tr>
				<tr class="item">
					<td>GST</td>
					<td><?php echo $model->gst; ?></td>
				</tr>
				<tr class="item last">
					<td>Cess</td>
					<td><?php echo $model->cess; ?></td>
				</tr>
				<tr class="total">
					<td></td>

					<td>&#8377;<?php echo $model->total_amount; ?></td>
				</tr>
			</table>
		</div>
	</body>
</html>
