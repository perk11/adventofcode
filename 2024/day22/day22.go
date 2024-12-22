package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"
)

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		total := 0
		lines := strings.Split(string(fileContents), "\n")
		for _, line := range lines {
			number, err := strconv.Atoi(line)
			if err != nil {
				panic(err)
			}
			nthNumber := getNthSecretNumber(number, 2000)
			println(strconv.Itoa(number) + ": " + strconv.Itoa(nthNumber))
			total += nthNumber
		}

		println(total)
	}
}
func getNthSecretNumber(startingNumber int, n int) int {
	for i := 0; i < n; i++ {
		startingNumber = getNextSecretNumber(startingNumber)
	}
	return startingNumber
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
