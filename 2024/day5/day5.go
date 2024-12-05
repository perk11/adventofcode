package main

import (
	"fmt"
	"os"
	"sort"
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
		lines := strings.Split(string(fileContents), "\n")
		sortRules := make(map[int]map[int]bool)
		var emptyLineIndex int
		var total int
		var total_pt2 int
		for index, line := range lines {
			if line == "" {
				emptyLineIndex = index
				break
			}
			parts := strings.Split(line, "|")
			if len(parts) != 2 {
				panic("invalid line: " + line)
			}
			firstNumber, err := strconv.Atoi(parts[0])
			if err != nil {
				panic("invalid first number: " + parts[0])
			}
			secondNumber, err := strconv.Atoi(parts[1])
			if err != nil {
				panic("invalid second number: " + parts[1])
			}

			_, ok := sortRules[firstNumber]
			if !ok {
				sortRules[firstNumber] = make(map[int]bool)
			}
			_, ok = sortRules[secondNumber]
			if !ok {
				sortRules[secondNumber] = make(map[int]bool)
			}

			sortRules[firstNumber][secondNumber] = true
			sortRules[secondNumber][firstNumber] = false
		}
		for index := emptyLineIndex + 1; index < len(lines); index++ {
			numbersToBeSortedStrings := strings.Split(lines[index], ",")
			numbersToBeSorted := make([]int, len(numbersToBeSortedStrings))

			for i, numStr := range numbersToBeSortedStrings {
				num, err := strconv.Atoi(numStr)
				if err != nil {
					panic(err)
				}
				numbersToBeSorted[i] = num
			}

			sortedNumbers := make([]int, len(numbersToBeSorted))
			copy(sortedNumbers, numbersToBeSorted)
			sort.Slice(sortedNumbers, func(i, j int) bool {
				return sortRules[sortedNumbers[i]][sortedNumbers[j]]
			})

			if compareSlices(sortedNumbers, numbersToBeSorted) {
				total += numbersToBeSorted[(len(numbersToBeSorted) / 2)]
			} else {
				total_pt2 += sortedNumbers[(len(sortedNumbers) / 2)]
			}
		}
		println(total)
		println(total_pt2)
	}
}

func compareSlices(slice1, slice2 []int) bool {
	// Check if lengths are different
	if len(slice1) != len(slice2) {
		return false
	}

	// Compare elements one by one
	for i := range slice1 {
		if slice1[i] != slice2[i] {
			return false
		}
	}

	return true
}
