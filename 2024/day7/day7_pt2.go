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
		var total int
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		for _, line := range lines {
			lineParts := strings.Split(line, ":")
			expressionResult, err := strconv.Atoi(lineParts[0])
			if err != nil {
				panic(err)
			}
			numbersStr := strings.Split(strings.Trim(lineParts[1], " "), " ")
			numbers := make([]int, len(numbersStr))
			for index, number := range numbersStr {
				numbers[index], err = strconv.Atoi(number)
				if err != nil {
					panic(err)
				}
			}
			possibleResults := getPossibleResults(numbers)
			for _, possibleResult := range possibleResults {
				if possibleResult == expressionResult {
					total += expressionResult
					break
				}
			}
		}

		println(total)
	}

}
func getPossibleResults(numbers []int) []int {
	var err error

	if len(numbers) == 2 {
		results := make([]int, 3)
		results[0] = numbers[0] * numbers[1]
		results[1] = numbers[0] + numbers[1]
		results[2], err = strconv.Atoi(strconv.Itoa(numbers[0]) + strconv.Itoa(numbers[1]))
		if err != nil {
			panic(err)
		}
		return results
	}
	possibleResults := getPossibleResults(numbers[:len(numbers)-1])
	newResults := make([]int, len(possibleResults)*3)
	for index, result := range possibleResults {
		newResults[index] = result * numbers[len(numbers)-1]
		newResults[index+len(possibleResults)] = result + numbers[len(numbers)-1]
		newResults[index+len(possibleResults)*2], err = strconv.Atoi(strconv.Itoa(result) + strconv.Itoa(numbers[len(numbers)-1]))
		if err != nil {
			panic(err)
		}
	}
	return newResults
}
