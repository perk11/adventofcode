package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"
)

func main() {
	filenames := []string{"testInput2", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		priceChanges := make([][]int, len(lines))
		prices := make([][]int, len(lines))
		buyers := make([]int, len(lines))
		for lineNumber, line := range lines {
			number, err := strconv.Atoi(line)
			if err != nil {
				panic(err)
			}
			buyers[lineNumber] = number
			priceChanges[lineNumber] = make([]int, 2000)
			prices[lineNumber] = make([]int, 2000)
			previousPrice := getPriceFromSecretNumber(number)
			prices[lineNumber][0] = previousPrice
			var price int
			for i := 0; i < 1999; i++ {
				number = getNextSecretNumber(number)
				price = getPriceFromSecretNumber(number)
				prices[lineNumber][i+1] = price
				priceChanges[lineNumber][i] = price - previousPrice
				previousPrice = price
			}
		}
		digitTotal := make(map[priceChangeSequence]int)
		for buyerIndex := 0; buyerIndex < len(priceChanges); buyerIndex++ {
			if buyerIndex%10 == 0 {
				println("Calculating buyer " + strconv.Itoa(buyerIndex))
			}
			for firstDigit := -9; firstDigit <= 9; firstDigit++ {
				for secondDigit := -9; secondDigit <= 9; secondDigit++ {
					if (firstDigit+secondDigit) > 9 || (firstDigit+secondDigit) < -9 {
						continue
					}
					for thirdDigit := -9; thirdDigit <= 9; thirdDigit++ {
						if (thirdDigit+secondDigit) > 9 || (thirdDigit+secondDigit) < -9 || firstDigit+secondDigit+thirdDigit > 9 || firstDigit+secondDigit+thirdDigit < -9 {
							continue
						}
					fourthDigitLoop:
						for fourthDigit := -9; fourthDigit <= 9; fourthDigit++ {
							if fourthDigit+thirdDigit > 9 ||
								fourthDigit+thirdDigit < -9 ||
								fourthDigit+thirdDigit+secondDigit > 9 ||
								fourthDigit+thirdDigit+secondDigit < -9 ||
								firstDigit+secondDigit+thirdDigit+fourthDigit > 9 ||
								firstDigit+secondDigit+thirdDigit+fourthDigit < -9 {
								continue
							}
							for i := 0; i < 1999-3; i++ {
								if priceChanges[buyerIndex][i] != firstDigit {
									continue
								}
								if priceChanges[buyerIndex][i+1] != secondDigit {
									continue
								}
								if priceChanges[buyerIndex][i+2] != thirdDigit {
									continue
								}
								if priceChanges[buyerIndex][i+3] != fourthDigit {
									continue
								}

								digitTotal[priceChangeSequence{firstDigit, secondDigit, thirdDigit, fourthDigit}] += prices[buyerIndex][i+4]
								continue fourthDigitLoop
							}
						}
					}
				}
			}
		}
		testSequence := priceChangeSequence{-2, 1, -1, 3}
		testTotal := digitTotal[testSequence]
		maxTotal := 0
		var maxIndex priceChangeSequence
		for index, thisTotal := range digitTotal {
			if thisTotal > maxTotal {
				maxTotal = thisTotal
				maxIndex = index
			}
		}
		println(testTotal)
		println(strconv.Itoa(maxIndex.firstDigit) + "," + strconv.Itoa(maxIndex.secondDigit) + "," + strconv.Itoa(maxIndex.thirdDigit) + "," + strconv.Itoa(maxIndex.fourthDigit))
		println(maxTotal)
	}
}

type priceChangeSequence struct {
	firstDigit  int
	secondDigit int
	thirdDigit  int
	fourthDigit int
}

func getPriceFromSecretNumber(number int) int {
	return number % 10
}
func getNextSecretNumber(startingNumber int) int {
	value := startingNumber * 64
	number := mix(value, startingNumber)
	number = prune(number)
	value = number / 32
	number = mix(value, number)
	number = prune(number)
	value = number * 2048
	number = mix(value, number)
	number = prune(number)
	return number
}
func mix(value int, number int) int {
	return value ^ number
}
func prune(number int) int {
	return number % 16777216
}
